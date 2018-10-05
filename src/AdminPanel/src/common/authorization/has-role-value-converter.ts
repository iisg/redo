import {UserRoleChecker} from "./user-role-checker";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class HasRoleValueConverter implements ToViewValueConverter {
  constructor(private userRoleChecker: UserRoleChecker) {
  }

  toView(role: string, resourceClass?: string): boolean {
    if (role) {
      const roleName = role + (resourceClass ? '-' + resourceClass : '_SOME_CLASS');
      return this.userRoleChecker.hasAll([roleName]);
    } else {
      return true;
    }
  }
}
