import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "./resource";
import {ApiRepository} from "common/repository/api-repository";
import {ResourceListQuery} from "./resource-list-query";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forOneMinute} from "../common/repository/cached-response";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'resources');
  }

  public getListQuery(): ResourceListQuery {
    return new ResourceListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  update(resource: Resource): Promise<Resource> {
    return this.postOne(resource);
  }

  applyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.patch(resource, {transitionId});
  }

  private postOne(entity: Resource) {
    return this.httpClient.post(this.oneEntityEndpoint(entity), this.toBackend(entity)).then(response => this.toEntity(response.content));
  }

  @cachedResponse(forOneMinute())
  public getHierarchy(id: number): Promise<Resource[]> {
    return this.httpClient.get(this.oneEntityEndpoint(id) + '/hierarchy').then(response => this.responseToEntities(response));
  }
}
