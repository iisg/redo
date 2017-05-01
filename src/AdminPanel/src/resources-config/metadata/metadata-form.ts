import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Metadata} from "./metadata";
import {computedFrom} from "aurelia-binding";
import {deepCopy} from "common/utils/object-utils";

@autoinject
export class MetadataForm {
  @bindable submit: (value: {savedMetadata: Metadata}) => Promise<any>;
  @bindable cancel: () => any = () => undefined;
  @bindable edit: Metadata;

  metadata: Metadata = new Metadata;
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  @computedFrom('metadata.id')
  get editing(): boolean {
    return !!this.metadata.id;
  }

  editChanged(newValue: Metadata) {
    this.metadata = $.extend(new Metadata(), deepCopy(newValue));
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({savedMetadata: this.metadata}))
          .then(() => this.editing || (this.metadata = new Metadata));
      }
    }).finally(() => this.submitting = false);
  }
}
