import {bindable, ComponentDetached} from "aurelia-templating";
import {twoWay} from "../../../../common/components/binding-mode";
import {computedFrom} from "aurelia-binding";

export class ValidPeselEditor {
  @bindable(twoWay) validPesel: boolean;
  @bindable originalValidPesel: boolean;
  @bindable hasBase: boolean;

  resetToOriginalValue() {
    this.validPesel = this.originalValidPesel;
  }

  @computedFrom('validPesel', 'originalValidPesel')
  get wasModified(): boolean {
    return this.validPesel != this.originalValidPesel;
  }
}
