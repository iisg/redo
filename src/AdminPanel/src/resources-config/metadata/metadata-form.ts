import {bindable, ComponentAttached} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Metadata} from "./metadata";
import {computedFrom} from "aurelia-binding";
import {changeHandler} from "common/components/binding-mode";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class MetadataForm implements ComponentAttached {
  @bindable submit: (value: {editedMetadata: Metadata}) => Promise<any>;
  @bindable cancel: () => void;
  @bindable(changeHandler('resetValues')) template: Metadata;
  @bindable edit: boolean = false;
  @bindable resourceClass: string;

  metadata: Metadata = new Metadata();
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, private entitySerializer: EntitySerializer) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  @computedFrom('metadata.id')
  get fromTemplate(): boolean {
    return this.template != undefined;
  }

  attached(): void {
    this.resetValues();
  }

  private resetValues() {
    this.metadata = this.template ? this.entitySerializer.clone(this.template) : new Metadata();
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return this.submit({editedMetadata: this.metadata})
          .then(() => this.resetValues());  // resets values to updated ones provided via binding
      }
    }).finally(() => this.submitting = false);
  }
}
