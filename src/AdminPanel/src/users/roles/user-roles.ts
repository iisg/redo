import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {UserRolesRepository} from "./user-roles-repository";
import {UserRole} from "./user-role";
import {deepCopy} from "../../common/utils/object-utils";

@autoinject
export class UserRoles implements ComponentAttached {
  addFormOpened: boolean = false;
  roles: Array<UserRole>;

  constructor(private userRolesRepository: UserRolesRepository) {
  }

  attached(): void {
    this.userRolesRepository.getList().then(roles => this.roles = roles);
  }

  addNewRole(newRole: UserRole) {
    return this.userRolesRepository.post(newRole).then(role => {
      this.addFormOpened = false;
      this.roles.push(role);
    });
  }

  saveEditedRole(role: UserRole, changedRole: UserRole): Promise<UserRole> {
    const originalRole = deepCopy(role);
    $.extend(role, changedRole);
    return this.userRolesRepository.put(changedRole).catch(() => $.extend(role, originalRole));
  }
}
