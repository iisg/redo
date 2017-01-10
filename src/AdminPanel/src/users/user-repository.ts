import {ApiRepository} from "../common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'users');
  }

  updateStaticPermissions(user: User, permissions: Array<string>): Promise<User> {
    return this.patch(user, {staticPermissions: permissions});
  }

  public toEntity(data: Object): User {
    return $.extend(new User(), data);
  }
}
