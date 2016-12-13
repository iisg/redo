import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {Metadata} from "./metadata";

@autoinject
export class MetadataForm {
  @bindable
  submit: (value: {metadata: Metadata}) => Promise<any>;

  metadata: Metadata = new Metadata;

  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({metadata: this.metadata}))
          .then(() => this.metadata = new Metadata);
      }
    }).finally(() => this.submitting = false);
  }
}
