import {bindable, ComponentAttached} from "aurelia-templating";
import {User} from "../user";
import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {computedFrom} from "aurelia-binding";
import {UserRoleRepository} from "../roles/user-role-repository";
import {UserRole} from "../roles/user-role";

@autoinject
export class UserRolesAssign implements ComponentAttached {
  @bindable user: User;

  private roles: Array<UserRole>;

  private selectedRoles: Array<UserRole> = [];

  constructor(private userRepository: UserRepository, private roleRepository: UserRoleRepository) {
  }

  attached() {
    this.roleRepository.getList().then(roles => {
      this.roles = roles;
      this.userChanged(this.user);
    });
  }

  @computedFrom('selectedRoles', 'selectedRoles.length')
  get rolesChanged() {
    return this.selectedRoles.length != this.user.roles.length
      || this.selectedRoles.map(role => role.systemRoleIdentifier).filter(role => this.user.roleIdentifiers.indexOf(role) < 0).length > 0;
  }

  userChanged(user: User) {
    if (this.roles) {
      this.selectedRoles = this.roles.filter(role => user.roleIdentifiers.indexOf(role.systemRoleIdentifier) >= 0);
    }
  }

  save(): Promise<any> {
    return this.userRepository.updateRoles(this.user, this.selectedRoles).then(user => {
      this.user = user;
    });
  }
}
