import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {UserRoleRepository} from "./user-role-repository";
import {UserRole} from "./user-role";
import {deepCopy} from "common/utils/object-utils";
import {DeleteEntityConfirmation} from "../../common/dialog/delete-entity-confirmation";
import {removeValue} from "../../common/utils/array-utils";

@autoinject
export class UserRoles implements ComponentAttached {
  addFormOpened: boolean = false;
  roles: Array<UserRole>;

  constructor(private userRoleRepository: UserRoleRepository, private deleteEntityConfirmation: DeleteEntityConfirmation) {
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

  deleteRole(userRole: UserRole) {
    this.deleteEntityConfirmation.confirm('role', userRole.name)
      .then(() => userRole.pendingRequest = true)
      .then(() => this.userRoleRepository.remove(userRole))
      .then(() => removeValue(this.roles, userRole))
      .finally(() => userRole.pendingRequest = false);
  }
}
