import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {Resource} from "../resources/resource";
import {EntitySerializer} from "common/dto/entity-serializer";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {cachedResponse, forSeconds} from "../common/repository/cached-response";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: DeduplicatingHttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, User, 'users');
  }

  @cachedResponse(forSeconds(30))
  public getList(): Promise<User[]> {
    return super.getList();
  }

  updateRoles(user: User, roleIds: Array<string>): Promise<User> {
    return this.patch(user, {roleIds});
  }

  @cachedResponse(forSeconds(30))
  getRelatedUser(resource: Resource): Promise<User> {
    const endpoint = `${this.endpoint}/byData/${resource.id}`;
    return this.httpClient.get(endpoint).then(response => this.toEntity(response.content));
  }
}
