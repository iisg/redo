import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Metadata} from "../metadata";
import {ValidationController} from "aurelia-validation";
import {computedFrom} from "aurelia-binding";

@autoinject
export class NoConfigurationConstraintDropdown {
  @bindable constraintNames: string[];
  @bindable metadata: Metadata;
  @bindable originalMetadata: Metadata;
  @bindable validationController: ValidationController;
  selectedConstraintNames: string[] = [];

  attached() {
    this.selectedConstraintNames = this.constraintNames.filter(name => !!this.metadata.constraints[name]);
  }

  onChange() {
    this.constraintNames.forEach(name => this.metadata.constraints[name] = (this.selectedConstraintNames.indexOf(name) >= 0));
  }

  resetToOriginalValue() {
    this.selectedConstraintNames = this.constraintNames.filter(name => !!this.originalMetadata.constraints[name]);
    this.onChange();
  }

  @computedFrom('selectedConstraintNames.length')
  get wasModified(): boolean {
    if (!this.originalMetadata) {
      return false;
    }
    const differentConstraints = this.constraintNames
      .filter(name => this.originalMetadata.constraints[name] !== (this.selectedConstraintNames.indexOf(name) >= 0));
    return differentConstraints.length !== 0;
  }
}
