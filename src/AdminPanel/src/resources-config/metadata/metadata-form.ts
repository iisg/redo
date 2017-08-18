import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Metadata} from "./metadata";
import {computedFrom} from "aurelia-binding";
import {ResourceKindRepository} from "../resource-kind/resource-kind-repository";
import {noop, VoidFunction} from "common/utils/function-utils";

@autoinject
export class MetadataForm {
  @bindable submit: (value: {editedMetadata: Metadata}) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable template: Metadata;
  @bindable edit: boolean = false;
  @bindable cancelButton: boolean = false;

  metadata: Metadata = new Metadata;
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, private resourceKindRepository: ResourceKindRepository) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  @computedFrom('metadata.id')
  get fromTemplate(): boolean {
    return this.template != undefined;
  }

  templateChanged() {
    this.resetValues();
  }

  private resetValues() {
    this.metadata = this.template ? Metadata.clone(this.template) : new Metadata();
    this.metadata.constraints.fetchResourceKindEntities(this.resourceKindRepository);
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({editedMetadata: this.metadata}))
          .then(() => this.resetValues());
      }
    }).finally(() => this.submitting = false);
  }
}
