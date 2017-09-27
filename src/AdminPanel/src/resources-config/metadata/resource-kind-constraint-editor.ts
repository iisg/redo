import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {arraysEqual} from "common/utils/array-utils";
import {twoWay} from "common/components/binding-mode";

export class ResourceKindConstraintEditor {
  @bindable(twoWay) selectedIds: number[];
  @bindable idsFromBaseMetadata: number[];
  @bindable(twoWay) disabled: boolean = false;

  resetToBaseIds() {
    this.selectedIds = (this.idsFromBaseMetadata || []).slice();
  }

  @computedFrom('selectedIds', 'idsFromBaseMetadata')
  get wasModified(): boolean {
    return !arraysEqual(this.selectedIds, (this.idsFromBaseMetadata || []));
  }

  @computedFrom('idsFromBaseMetadata', 'disabled')
  get canInherit(): boolean {
    return !this.disabled && this.idsFromBaseMetadata != undefined;
  }
}
