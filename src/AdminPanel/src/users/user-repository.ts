import {ApiRepository} from "common/repository/api-repository";
import {autoinject} from "aurelia-dependency-injection";
import {User} from "./user";
import {HttpClient} from "aurelia-http-client";
import {UserRoleRepository} from "./roles/user-role-repository";
import {ResourceRepository} from "../resources/resource-repository";
import {Resource} from "../resources/resource";

@autoinject
export class UserRepository extends ApiRepository<User> {
  constructor(httpClient: HttpClient, private userRoleRepository: UserRoleRepository, private resourceRepository: ResourceRepository) {
    super(httpClient, 'users');
  }

  public toEntity(data: Object): Promise<User> {
    let user: User = $.extend(new User(), data);
    user.roles = user.roles.map(role => this.userRoleRepository.toEntity(role));
    return this.resourceRepository.toEntity(user.userData).then(userData => {
      user.userData = userData;
    }).then(() => user);
  }

  updateRoles(user: User, roleIds: Array<string>): Promise<User> {
    return this.patch(user, {roleIds});
  }

  getRelatedUser(resource: Resource): Promise<User> {
    const endpoint = `${this.endpoint}/byData/${resource.id}`;
    return this.httpClient.get(endpoint).then(response => this.toEntity(response.content));
  }
}
