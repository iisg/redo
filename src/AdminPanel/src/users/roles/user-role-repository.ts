import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {UserRole} from "./user-role";

@autoinject
export class UserRoleRepository extends ApiRepository<UserRole> {
  constructor(httpClient: HttpClient) {
    super(httpClient, 'user-roles');
  }

  public toEntity(data: Object): UserRole {
    return $.extend(new UserRole(), data);
  }
}
