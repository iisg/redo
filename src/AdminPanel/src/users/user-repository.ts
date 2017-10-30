import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";
import {Resource} from "../resources/resource";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient, entitySerializer: EntitySerializer) {
    super(httpClient, entitySerializer, User, 'users');
  }

  updateRoles(user: User, roleIds: Array<string>): Promise<User> {
    return this.patch(user, {roleIds});
  }

  getRelatedUser(resource: Resource): Promise<User> {
    const endpoint = `${this.endpoint}/byData/${resource.id}`;
    return this.httpClient.get(endpoint).then(response => this.toEntity(response.content));
  }
}
