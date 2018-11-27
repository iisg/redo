import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "../../../../common/components/binding-mode";

export class DisplayStrategyConstraintEditor {
  @bindable(twoWay) value: string;
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
