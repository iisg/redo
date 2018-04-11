import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {arraysEqual} from "common/utils/array-utils";
import {oneTime, twoWay} from "common/components/binding-mode";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";

@autoinject
export class ResourceKindConstraintEditor {
  @bindable(twoWay) metadata: Metadata;
  @bindable(oneTime) idsFromOriginalMetadata: number[];
  @bindable(twoWay) disabled: boolean = false;
  @bindable hasBase: boolean;
  @bindable resourceClass: string;

  resetToOriginalIds() {
    this.metadata.constraints.resourceKind = (this.idsFromOriginalMetadata || []).slice();
  }

  @computedFrom('metadata.constraints.resourceKind', 'idsFromOriginalMetadata')
  get wasModified(): boolean {
    return !arraysEqual(this.metadata.constraints.resourceKind, (this.idsFromOriginalMetadata || []));
  }

  @computedFrom('hasBase', 'disabled')
  get canInherit(): boolean {
    return !this.disabled && this.hasBase;
  }
}
