import {ApiRepository} from "../common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";
import {propertyNamesToCamelCase} from "../common/repository/repository-utils";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'users');
  }

  updateStaticPermissions(user: User, permissions: Array<string>): Promise<User> {
    return this.patch(user, {static_permissions: permissions});
  }

  public toEntity(data: Object): User {
    return $.extend(new User(), propertyNamesToCamelCase(data));
  }
}
