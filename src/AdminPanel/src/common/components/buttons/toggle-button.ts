import {bindable} from "aurelia-templating";

export class ToggleButton {
  @bindable primaryIconName: string;
  @bindable primaryLabel: string;
  @bindable secondaryIconName: string;
  @bindable secondaryLabel: string;
  @bindable entityName: string;
  @bindable showTooltipsInsteadOfLabels: boolean;
  @bindable toggled: boolean;
  @bindable disabled: boolean;
  @bindable submitting: boolean;
  @bindable disabilityReason: DisabilityReason;

  stopIfDisabled(event: Event) {
    if (this.disabled) {
      event.stopPropagation();
    }
  }
}

export interface DisabilityReason {
  icon: string;
  message: string;
  details?: string;
}
