import {autoinject} from "aurelia-dependency-injection";
import {UserRoleChecker} from "./user-role-checker";

@autoinject
export class RequireRoleCustomAttribute {
  private requiredRoles: Array<string> = [];

  constructor(private userRoleChecker: UserRoleChecker, private element: Element) {
  }

  valueChanged(roles: string|Array<string>) {
    if (roles instanceof Array) {
      this.requiredRoles = roles;
    } else if (typeof roles == 'string') {
      this.requiredRoles = (roles as string).split(',');
    } else {
      this.requiredRoles = [];
    }
    this.requiredRoles = this.requiredRoles.map(p => p.trim()).filter(p => !!p);
    this.updateView();
  }

  updateView() {
    $(this.element).toggle(this.userRoleChecker.hasAll(this.requiredRoles));
  }
}
