import {bindable} from "aurelia-templating";
import {oneTime} from "common/components/binding-mode";
import {RestrictingMetadataIdMap, RequirementState} from "workflows/workflow";

export class ReadOnlyExcelCheckbox {
  @bindable(oneTime) value: string;

  private states: RestrictingMetadataIdMap = {0: RequirementState.OPTIONAL};

  valueChanged(): void {
    this.states[0] = RequirementState[this.value.toUpperCase()];
  }
}
