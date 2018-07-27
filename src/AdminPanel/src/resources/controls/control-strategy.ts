import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {SingleMetadataValueValidator} from "../../common/validation/rules/single-metadata-value-validator";
import {ValidationController} from "aurelia-validation";
import {MetadataValue} from "../metadata-value";
import {MetadataControl} from "../../resources-config/metadata/metadata-control";

@autoinject
export class ControlStrategy {
  metadata: Metadata;
  resource: Resource;
  metadataValue: MetadataValue;
  disabled: boolean = false;
  validationRules: any;
  validationController: ValidationController;

  constructor(private singleMetadataValueValidator: SingleMetadataValueValidator) {
  }

  activate(model: Object) {
    $.extend(this, model);
    if (this.metadata) {
      this.validationRules = this.singleMetadataValueValidator.createRules(this.metadata, this.resource).rules;
      if (this.metadata.control == MetadataControl.BOOLEAN && !this.metadataValue.value) {
        this.metadataValue.value = false; // forces "undefined" boolean values to be false
      }
    }
  }

  detached() {
    if (this.validationController) {
      this.validationController.reset({object: this.metadataValue, propertyName: 'value'});
    }
  }
}
