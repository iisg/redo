import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "../../resources/resource";
import {User} from "../user";
import {UserRepository} from "../user-repository";
import {computedFrom} from "aurelia-binding";

@autoinject
export class UserRoles {
  @bindable userData: Resource;
  private user: User;

  constructor(private userRepository: UserRepository) {
  }

  async userDataChanged() {
    this.user = await this.userRepository.getRelatedUser(this.userData);
  }

  @computedFrom("user")
  get adminInClasses(): string[] {
    return this.resourceClassesFromRoles('ADMIN');
  }

  @computedFrom("user")
  get operatorInClasses(): string[] {
    return this.resourceClassesFromRoles('OPERATOR');
  }

  private resourceClassesFromRoles(prefix: 'ADMIN' | 'OPERATOR'): string[] {
    if (this.user) {
      return this.user.roles.filter(role => role.indexOf(prefix + '-') === 0).map(role => role.substr(prefix.length + 1));
    } else {
      return [];
    }
  }
}
