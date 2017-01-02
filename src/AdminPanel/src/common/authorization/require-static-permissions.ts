import {autoinject} from "aurelia-dependency-injection";
import {StaticPermissionsChecker} from "./static-permissions-checker";

@autoinject
export class RequireStaticPermissionsCustomAttribute {
  private requiredPermissions: Array<string> = [];

  constructor(private staticPermissionsChecker: StaticPermissionsChecker, private element: Element) {
  }

  valueChanged(permissions: string|Array<string>) {
    if (permissions instanceof Array) {
      this.requiredPermissions = permissions;
    } else if (typeof permissions == 'string') {
      this.requiredPermissions = (permissions as string).split(',');
    } else {
      this.requiredPermissions = [];
    }
    this.requiredPermissions = this.requiredPermissions.map(p => p.trim()).filter(p => !!p);
    this.updateView();
  }

  updateView() {
    $(this.element).toggle(this.staticPermissionsChecker.allAllowed(this.requiredPermissions));
  }
}