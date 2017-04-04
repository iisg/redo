import {ApiRepository} from "../common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";
import {UserRole} from "./roles/user-role";
import {UserRoleRepository} from "./roles/user-role-repository";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient, private userRoleRepository: UserRoleRepository) {
    super(httpClient, 'users');
  }

  public toEntity(data: Object): User {
    let user = $.extend(new User(), data);
    user.roles = user.roles.map(role => this.userRoleRepository.toEntity(role));
    return user;
  }

  updateRoles(user: User, roles: Array<UserRole>) {
    return this.patch(user, {roleIds: roles.map(role => role.id)});
  }
}
