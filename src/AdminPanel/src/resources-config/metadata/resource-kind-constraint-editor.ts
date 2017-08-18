import {bindable} from "aurelia-templating";
import {bindingMode, computedFrom} from "aurelia-binding";
import {ResourceKind} from "../resource-kind/resource-kind";
import {arraysEqual} from "common/utils/array-utils";

export class ResourceKindConstraintEditor {
  @bindable({defaultBindingMode: bindingMode.twoWay}) values: ResourceKind[];
  @bindable({defaultBindingMode: bindingMode.twoWay}) baseValues: ResourceKind[];
  @bindable({defaultBindingMode: bindingMode.twoWay}) disabled: boolean = false;

  private resetToBaseValues() { // tslint:disable-line
    this.values = (this.baseValues || []).slice();
  }

  @computedFrom('values', 'baseValues')
  get wasModified(): boolean {
    return !arraysEqual(this.values, (this.baseValues || []));
  }

  @computedFrom('baseValues', 'disabled')
  get canInherit(): boolean {
    return !this.disabled && this.baseValues != undefined;
  }
}
