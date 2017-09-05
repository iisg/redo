import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {UserRoleRepository} from "../roles/user-role-repository";
import {UserRole} from "../roles/user-role";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class UserRolesChooser implements ComponentAttached {
  @bindable(twoWay) roleIds: Array<string> = [];

  availableRoles: Array<UserRole>;

  constructor(private roleRepository: UserRoleRepository) {
  }

  attached() {
    this.roleRepository.getList().then(availableRoles => {
      this.availableRoles = availableRoles;
    });
  }
}
