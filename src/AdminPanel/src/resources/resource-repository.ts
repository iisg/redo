import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "./resource";
import {ApiRepository} from "common/repository/api-repository";
import {ResourceListQuery} from "./resource-list-query";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class ResourceRepository extends ApiRepository<Resource> {
  constructor(httpClient: HttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, Resource, 'resources');
  }

  public getListQuery(): ResourceListQuery {
    return new ResourceListQuery(this.httpClient, this.endpoint, this.entitySerializer);
  }

  public getByParent(parent: Resource): Promise<Resource[]> {
    const parentId: number = (parent == undefined) ? 0 : parent.id;
    const endpoint = this.endpoint + `/${parentId}/resources`;
    return this.httpClient.get(endpoint).then(response => this.responseToEntities(response));
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
}
