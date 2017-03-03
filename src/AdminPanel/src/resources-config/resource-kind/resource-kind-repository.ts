import {HttpClient} from "aurelia-http-client";
import {autoinject, Container, NewInstance} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {deepCopy} from "../../common/utils/object-utils";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ApiRepository} from "../../common/repository/api-repository";
import {ResourceKindMetadata} from "../metadata/metadata";
import {cachedResponse} from "../../common/repository/cached-response";

@autoinject
export class ResourceKindRepository extends ApiRepository<ResourceKind> {
  constructor(httpClient: HttpClient, private metadataRepository: MetadataRepository, private container: Container) {
    super(httpClient, 'resource-kinds');
  }

  public get(id: number): Promise<ResourceKind> {
    return this.getList().then(resourceKindList => resourceKindList.filter(rk => rk.id == id)[0]);
  }

  @cachedResponse(30000)
  public getList(): Promise<ResourceKind[]> {
    return super.getList();
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
    for (let metadata of data.metadataList) {
      metadata.base_id = metadata.base.id;
      delete metadata.base;
    }
    delete data.workflow;
    return data;
  }

  public toEntity(data: Object): Promise<ResourceKind> {
    let resourceKind = this.container.get(NewInstance.of(ResourceKind));
    return this.metadataRepository.getList().then(metadataList => {
      data['metadataList'] = data['metadataList'].map(metadataData => {
        const baseId = metadataData['baseId'];
        delete metadataData['baseId'];
        let metadata = this.metadataRepository.toEntity(metadataData) as ResourceKindMetadata;
        metadata.base = metadataList.filter(m => m.id == baseId)[0];
        return metadata;
      });
      return $.extend(resourceKind, data);
    });
  }
}
