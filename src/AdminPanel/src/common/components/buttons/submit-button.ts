import {bindable, customElement, useView} from "aurelia-templating";
import {booleanAttribute} from "../boolean-attribute";
import {ToggleButton} from "./toggle-button";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@useView('./toggle-button.html')
@customElement('submit-button')
@autoinject
export class SubmitButton extends ToggleButton {
  @bindable @booleanAttribute editing: boolean;
  @bindable @booleanAttribute submitting: boolean;

  constructor(i18n: I18N) {
    super(i18n);
    this.type = "submit";
    this.primaryIconName = 'add';
    this.primaryLabel = 'Add';
    this.secondaryIconName = 'accept-2';
    this.secondaryLabel = 'Apply';
  }

  attached() {
    this.submittingChanged();
    this.editingChanged();
  }

  submittingChanged() {
    this.disabled = this.throbberDisplayed = this.submitting;
  }

  editingChanged() {
    this.toggled = this.editing;
  }
}
