import {HttpClient} from "aurelia-http-client";
import {autoinject, Container, NewInstance} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {deepCopy} from "common/utils/object-utils";
import {MetadataRepository} from "../metadata/metadata-repository";
import {cachedResponse} from "common/repository/cached-response";
import {SystemMetadata} from "../metadata/system-metadata";
import {ResourceClassApiRepository} from "../../common/repository/resource-class-api-repository";

@autoinject
export class ResourceKindRepository extends ResourceClassApiRepository<ResourceKind> {
  constructor(httpClient: HttpClient, private metadataRepository: MetadataRepository, private container: Container) {
    super(httpClient, 'resource-kinds');
  }

  public getResourceKind(id: number): Promise<ResourceKind> {
    return this.getList().then(resourceKindList => resourceKindList.filter(rk => rk.id == id)[0]);
  }

  public getListByClass(resourceClass: string): Promise<ResourceKind[]> {
    return super.getListByClass(resourceClass);
  }

  @cachedResponse(30000)
  public getList(): Promise<ResourceKind[]> {
    return this.httpClient.get(this.endpoint).then(response => this.responseToEntities(response));
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
