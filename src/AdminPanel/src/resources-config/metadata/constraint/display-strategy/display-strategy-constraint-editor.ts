import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

export class DisplayStrategyConstraintEditor {
  @bindable value: string;
  @bindable originalValue: string;
  @bindable hasBase: boolean;

  @computedFrom('value', 'originalValue')
  get wasModified(): boolean {
    return this.value != this.originalValue;
  }

  resetToOriginalValues() {
    this.value = this.originalValue;
  }
}
