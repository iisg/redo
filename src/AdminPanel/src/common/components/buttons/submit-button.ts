import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable, customElement, useView} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('submit-button')
@autoinject
export class SubmitButton extends ToggleButton {
  @bindable editing: boolean;
  @bindable submitting: boolean;

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
