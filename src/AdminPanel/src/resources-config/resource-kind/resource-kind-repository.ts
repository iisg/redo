import {HttpClient} from "aurelia-http-client";
import {autoinject, Container, NewInstance} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {deepCopy} from "common/utils/object-utils";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ApiRepository} from "common/repository/api-repository";
import {cachedResponse} from "common/repository/cached-response";
import {SystemMetadata} from "../metadata/system-metadata";

@autoinject
export class ResourceKindRepository extends ApiRepository<ResourceKind> {
  constructor(httpClient: HttpClient, private metadataRepository: MetadataRepository, private container: Container) {
    super(httpClient, 'resource-kinds');
  }

  public get(id: number): Promise<ResourceKind> {
    return this.getListWithSystemResourceKinds().then(resourceKindList => resourceKindList.filter(rk => rk.id == id)[0]);
  }

  @cachedResponse(30000)
  public getList(): Promise<ResourceKind[]> {
    return super.getList();
  }

  @cachedResponse(30000)
  public getListWithSystemResourceKinds(): Promise<ResourceKind[]> {
    return this.httpClient
      .createRequest(this.endpoint)
      .asGet()
      .withParams({systemResourceKind: true})
      .send()
      .then(response => Promise.all(response.content.map(item => this.toEntity(item))));
  }

  public update(updatedResourceKind: ResourceKind): Promise<ResourceKind> {
    let backendRepresentation = this.toBackend(updatedResourceKind);
    return this.patch(updatedResourceKind, {
      label: updatedResourceKind.label,
      metadataList: backendRepresentation['metadataList'],
    });
  }

  protected toBackend(entity: ResourceKind): Object {
    let data = deepCopy(entity);
    data.metadataList = data.metadataList
      .map(metadata => this.metadataRepository.toBackend(metadata))
      .filter(metadata => metadata.baseId != -1);
    delete data.workflow;
    return data;
  }

  public toEntity(data: Object): Promise<ResourceKind> {
    let resourceKind = this.container.get(NewInstance.of(ResourceKind));
    data['metadataList'] = data['metadataList'].map(this.metadataRepository.toEntity);
    data['metadataList'] = [SystemMetadata.PARENT].concat(data['metadataList']);
    return $.extend(resourceKind, data);
  }
}
