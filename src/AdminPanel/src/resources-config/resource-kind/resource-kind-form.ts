import {ResourceKind} from "./resource-kind";
import {BootstrapSelectChangeEvent} from "../bootstrap-select/bootstrap-select";
import {Metadata, ResourceKindMetadata} from "../metadata/metadata";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class ResourceKindForm {
  @bindable
  submit: (value: {resourceKind: ResourceKind}) => Promise<any>;

  resourceKind: ResourceKind = new ResourceKind;

  submitting = false;

  sortingMetadata = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  addNewMetadata(event: BootstrapSelectChangeEvent<Metadata>) {
    let metadata = new ResourceKindMetadata;
    metadata.base = event.detail.value;
    this.resourceKind.metadataList.push(metadata);
  }

  removeMetadata(metadata: ResourceKindMetadata) {
    this.resourceKind.metadataList.splice(this.resourceKind.metadataList.indexOf(metadata), 1);
  }

  isSortingMetadata() {
    return this.sortingMetadata;
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return this.submit({resourceKind: this.resourceKind})
          .then(() => this.resourceKind = new ResourceKind);
      }
    }).finally(() => this.submitting = false);
  }
}
