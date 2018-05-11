import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {cachedResponse, forSeconds} from "common/repository/cached-response";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {ResourceKindListQuery} from "./resource-kind-list-query";
import {ApiRepository} from "../../common/repository/api-repository";

@autoinject
export class ResourceKindRepository extends ApiRepository<ResourceKind> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, ResourceKind, 'resource-kinds');
  }

  public getListQuery(): ResourceKindListQuery {
    return new ResourceKindListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  @cachedResponse(forSeconds(30))
  public get(id: number|string, suppressError?: boolean): Promise<ResourceKind> {
    return super.get(id, suppressError);
  }

  public update(updatedResourceKind: ResourceKind): Promise<ResourceKind> {
    let backendRepresentation = this.toBackend(updatedResourceKind);
    return this.patch(updatedResourceKind, {
      label: updatedResourceKind.label,
      metadataList: backendRepresentation['metadataList'],
      displayStrategies: backendRepresentation['displayStrategies'],
    });
  }
}
