import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {computedFrom} from "aurelia-binding";

export class RegexConstraintEditor {
  @bindable(twoWay) regex: string;
  @bindable originalRegex: string;

  resetToOriginalValues() {
    this.regex = this.originalRegex;
  }

  @computedFrom('regex', 'originalRegex')
  get wasModified(): boolean {
    return this.regex != this.originalRegex;
  }

  @computedFrom('originalRegex')
  get canInherit(): boolean {
    return this.originalRegex != undefined;
  }
}
