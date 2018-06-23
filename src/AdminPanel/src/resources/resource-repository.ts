import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "./resource";
import {ApiRepository} from "common/repository/api-repository";
import {ResourceListQuery} from "./resource-list-query";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forOneMinute} from "../common/repository/cached-response";
import {suppressError as suppressErrorHeader} from "../common/http-client/headers";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'resources');
  }

  public getListQuery(): ResourceListQuery {
    return new ResourceListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  update(resource: Resource): Promise<Resource> {
    return this.updateAndApplyTransition(resource, '');
  }

  updateAndApplyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.httpClient.post(this.oneEntityEndpoint(resource) + `?transitionId=${transitionId}`, this.toBackend(resource))
      .then(response => this.toEntity(response.content));
  }

  updateResourceWithNoValidation(resource: Resource, newKindId: number, placesIds: string[]): Promise<Resource> {
    const params = {newKindId, placesIds};
    return this.httpClient.createRequest(this.oneEntityEndpoint(resource))
      .asPost()
      .withHeader("GOD-EDIT", "true")
      .withContent(this.toBackend(resource))
      .withParams(params)
      .send()
      .then(response => this.toEntity(response.content));
  }

  @cachedResponse(forOneMinute())
  public getHierarchy(id: number): Promise<Resource[]> {
    const request = this.httpClient.createRequest(this.oneEntityEndpoint(id) + '/hierarchy').asGet();
    request.withHeader(suppressErrorHeader.name, suppressErrorHeader.value);
    return request.send()
      .then(response => this.responseToEntities(response))
      .catch(() => []);
  }
}
