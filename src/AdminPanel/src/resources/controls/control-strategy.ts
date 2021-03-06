import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {SingleMetadataValueValidator} from "../../common/validation/rules/single-metadata-value-validator";
import {ValidationController} from "aurelia-validation";
import {MetadataValue} from "../metadata-value";

@autoinject
export class ControlStrategy {
  metadata: Metadata;
  resource: Resource;
  metadataValue: MetadataValue;
  disabled: boolean = false;
  validationRules: any;
  validationController: ValidationController;
  skipValidation: boolean = false;
  required: boolean = false;

  constructor(private singleMetadataValueValidator: SingleMetadataValueValidator) {
  }

  activate(model: Object) {
    $.extend(this, model);
    if (this.metadata) {
      this.validationRules = this.singleMetadataValueValidator.createRules(this.metadata, this.resource, this.required).rules;
    }
  }

  detached() {
    if (this.validationController) {
      this.validationController.reset({object: this.metadataValue, propertyName: 'value'});
    }
  }
}
