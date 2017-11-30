import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {cachedResponse} from "common/repository/cached-response";
import {ResourceClassApiRepository} from "common/repository/resource-class-api-repository";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class ResourceKindRepository extends ResourceClassApiRepository<ResourceKind> {
  constructor(httpClient: HttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, ResourceKind, 'resource-kinds');
  }

  @cachedResponse(30000)
  public get(id: number|string, suppressError: boolean = false): Promise<ResourceKind> {
    return super.get(id, suppressError);
  }

  @cachedResponse(30000)
  public getListByClass(resourceClass: string): Promise<ResourceKind[]> {
    return super.getListByClass(resourceClass);
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
}
