import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {computedFrom} from "aurelia-binding";

export class RegexConstraintEditor {
  @bindable(twoWay) regex: string;
  @bindable baseRegex: string;

  resetToBaseValues() {
    this.regex = this.baseRegex;
  }

  @computedFrom('regex', 'baseRegex')
  get wasModified(): boolean {
    return this.regex != this.baseRegex;
  }

  @computedFrom('baseRegex')
  get canInherit(): boolean {
    return this.baseRegex != undefined;
  }
}
