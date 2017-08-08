import {bindable} from "aurelia-templating";
import {User} from "../user";
import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";

@autoinject
export class UserRolesAssign {
  @bindable user: User;

  rolesChanged = false;

  userRoleIds: Array<string> = [];

  constructor(private userRepository: UserRepository) {
  }

  userChanged() {
    this.userRoleIds = this.user.roles.map(r => r.id);
  }

  save(): Promise<any> {
    return this.userRepository.updateRoles(this.user, this.userRoleIds).then(user => {
      this.user = user;
    });
  }
}
