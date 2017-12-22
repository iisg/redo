import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {ValueWrapper} from "common/utils/value-wrapper";
import {autoinject} from "aurelia-dependency-injection";
import {SingleMetadataValueValidator} from "../../common/validation/rules/single-metadata-value-validator";
import {ValidationController} from "aurelia-validation";

@autoinject
export class ControlStrategy {
  metadata: Metadata;
  resource: Resource;
  valueWrapper: ValueWrapper<any>;
  disabled: boolean = false;
  validationRules: any;
  validationController: ValidationController;

  constructor(private singleMetadataValueValidator: SingleMetadataValueValidator) {
  }

  activate(model: Object) {
    $.extend(this, model);
    if (this.metadata) {
      this.validationRules = this.singleMetadataValueValidator.createRules(this.metadata, this.resource).rules;
      if (this.metadata.control == 'boolean' && !this.valueWrapper.value) {
        this.valueWrapper.value = false; // forces "undefined" boolean values to be false
      }
    }
  }

  detached() {
    if (this.validationController) {
      this.validationController.reset({object: this.valueWrapper, propertyName: 'value'});
    }
  }
}
