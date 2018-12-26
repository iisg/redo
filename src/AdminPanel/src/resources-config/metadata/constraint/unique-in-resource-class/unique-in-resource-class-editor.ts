import {bindable} from "aurelia-templating";
import {twoWay} from "../../../../common/components/binding-mode";
import {computedFrom} from "aurelia-binding";

export class UniqueInResourceClassEditor {
  @bindable(twoWay) unique: boolean;
  @bindable originalUnique: boolean;
  @bindable hasBase: boolean;

  resetToOriginalValue() {
    this.unique = this.originalUnique;
  }

  @computedFrom('unique', 'originalUnique')
  get wasModified(): boolean {
    return this.unique != this.originalUnique;
  }
}