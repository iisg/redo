import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {deepCopy} from "common/utils/object-utils";
import {UserRole} from "./user-role";
import {computedFrom} from "aurelia-binding";

@autoinject
export class UserRoleForm {
  @bindable submit: (value: {savedRole: UserRole}) => Promise<any>;
  @bindable cancel: () => any = () => undefined;
  @bindable edit: UserRole;

  role: UserRole = new UserRole();
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  @computedFrom('role.id')
  get editing(): boolean {
    return !!this.role.id;
  }

  editChanged(newValue: UserRole) {
    this.role = $.extend(new UserRole(), deepCopy(newValue));
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({savedRole: this.role}))
          .then(() => this.editing || (this.role = new UserRole()));
      }
    }).finally(() => this.submitting = false);
  }
}
