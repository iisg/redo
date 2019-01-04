import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "./resource";
import {ApiRepository} from "common/repository/api-repository";
import {ResourceListQuery} from "./resource-list-query";
import {ResourceTreeQuery} from './resource-tree-query';
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forOneMinute, forSeconds} from "../common/repository/cached-response";
import {suppressError as suppressErrorHeader} from "../common/http-client/headers";
import {debouncePromise} from "../common/utils/function-utils";
import {keyBy} from "lodash";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  private resourceIdsToQueryForTeaser = [];

  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'resources');
  }

  public getListQuery(): ResourceListQuery {
    return new ResourceListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  public getTreeQuery(): ResourceTreeQuery {
    return new ResourceTreeQuery(this.httpClient, `${this.endpoint}/tree`, this.entitySerializer);
  }

  @cachedResponse(forSeconds(30))
  public getTeaser(id: number): Promise<Resource> {
    if (this.resourceIdsToQueryForTeaser.indexOf(id) === -1) {
      this.resourceIdsToQueryForTeaser.push(id);
    }
    return this.fetchTeasers().then(teasers => teasers[id]);
  }

  private fetchTeasers = debouncePromise(() => {
    if (this.resourceIdsToQueryForTeaser.length) {
      const endpoint = 'teasers/' + this.resourceIdsToQueryForTeaser.join(',');
      const request = this.httpClient.createRequest(this.oneEntityEndpoint(endpoint))
        .asGet()
        .withHeader(suppressErrorHeader.name, suppressErrorHeader.value);
      this.resourceIdsToQueryForTeaser = [];
      return request.send()
        .then(response => this.responseToEntities(response))
        .then(entities => keyBy(entities, 'id'));
    }
  }, 100);

  update(resource: Resource): Promise<Resource> {
    return this.updateAndApplyTransition(resource, '');
  }

  updateAndApplyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.httpClient.put(this.oneEntityEndpoint(resource) + `?transitionId=${transitionId}`, this.toBackend(resource))
      .then(response => this.toEntity(response.content));
  }

  updateResourceWithNoValidation(resource: Resource, newKindId: number, placesIds: string[]): Promise<Resource> {
    const params = {newKindId, placesIds};
    return this.httpClient.createRequest(this.oneEntityEndpoint(resource))
      .asPut()
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

  public evaluateDisplayStrategy(id: number, template: string): Promise<string> {
    const request = this.httpClient.createRequest(this.oneEntityEndpoint(id) + '/evaluate-display-strategy').asPatch();
    request.withContent({template});
    request.withHeader(suppressErrorHeader.name, suppressErrorHeader.value);
    return request.send()
      .then(response => response.content.result)
      .catch(() => 'ERROR');
  }
}
