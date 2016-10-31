import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {BindingEngine, bindingMode, computedFrom} from "aurelia-binding";
import {Metadata} from "./metadata";

@autoinject
export class MetadataForm {
  @bindable
  metadata: Metadata;

  @bindable
  submit: () => Promise<any>;

  @bindable({defaultBindingMode: bindingMode.twoWay})
  validating: boolean;

  private submitting: boolean = false;

  controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, bindingEngine: BindingEngine) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
    bindingEngine.propertyObserver(this.controller, 'validating').subscribe((validating) => this.validating = validating);
  }

  @computedFrom('controller.validating', 'submitting')
  get isRequesting() {
    return this.controller.validating || this.submitting;
  }

  validateMe() {
    this.controller.validate().then(errors => {
      if (errors.length == 0) {
        let promise = this.submit();
        if (promise) {
          this.submitting = true;
          promise.finally(() => this.submitting = false);
        }
      }
    });
  }
}
