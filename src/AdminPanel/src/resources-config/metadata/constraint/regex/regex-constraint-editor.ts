import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {computedFrom} from "aurelia-binding";

export class RegexConstraintEditor {
  @bindable(twoWay) regex: string;
  @bindable originalRegex: string;
  @bindable hasBase: boolean;

  resetToOriginalValues() {
    this.regex = this.originalRegex;
  }

  @computedFrom('regex', 'originalRegex')
  get wasModified(): boolean {
    return this.regex != this.originalRegex;
  }
}
