import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {UserRoleRepository} from "./user-role-repository";
import {UserRole} from "./user-role";
import {deepCopy} from "common/utils/object-utils";

@autoinject
export class UserRoles implements ComponentAttached {
  addFormOpened: boolean = false;
  roles: Array<UserRole>;

  constructor(private userRoleRepository: UserRoleRepository) {
  }

  attached(): void {
    this.userRoleRepository.getList().then(roles => this.roles = roles);
  }

  addNewRole(newRole: UserRole) {
    return this.userRoleRepository.post(newRole).then(role => {
      this.addFormOpened = false;
      this.roles.push(role);
    });
  }

  saveEditedRole(role: UserRole, changedRole: UserRole): Promise<UserRole> {
    const originalRole = deepCopy(role);
    $.extend(role, changedRole);
    return this.userRoleRepository.put(changedRole).catch(() => $.extend(role, originalRole));
  }
}
