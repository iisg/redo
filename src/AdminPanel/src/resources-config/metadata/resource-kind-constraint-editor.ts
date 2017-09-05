import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {ResourceKind} from "../resource-kind/resource-kind";
import {arraysEqual} from "common/utils/array-utils";
import {twoWay} from "common/components/binding-mode";

export class ResourceKindConstraintEditor {
  @bindable(twoWay) values: ResourceKind[];
  @bindable(twoWay) baseValues: ResourceKind[];
  @bindable(twoWay) disabled: boolean = false;

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
