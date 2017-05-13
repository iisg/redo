import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";
import {UserRoleRepository} from "./roles/user-role-repository";
import {ResourceRepository} from "../resources/resource-repository";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient, private userRoleRepository: UserRoleRepository, private resourceRepository: ResourceRepository) {
    super(httpClient, 'users');
  }

  public async toEntity(data: Object): Promise<User> {
    let user = $.extend(new User(), data);
    user.roles = user.roles.map(role => this.userRoleRepository.toEntity(role));
    user.userData = await this.resourceRepository.toEntity(user.userData);
    return user;
  }

  updateRoles(user: User, roleIds: Array<string>): Promise<User> {
    return this.patch(user, {roleIds});
  }
}
