import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {UserRole} from "./user-role";
import {computedFrom} from "aurelia-binding";
import {noop, VoidFunction} from "common/utils/function-utils";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class UserRoleForm {
  @bindable submit: (value: {savedRole: UserRole}) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable edit: UserRole;

  role: UserRole = new UserRole();
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, private entitySerializer: EntitySerializer) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  @computedFrom('role.id')
  get editing(): boolean {
    return !!this.role.id;
  }

  editChanged(newValue: UserRole) {
    this.role = this.entitySerializer.clone(newValue);
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
