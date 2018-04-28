import {customElement, useView} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@useView('./toggle-button.html')
@customElement('edit-button')
@autoinject
export class EditButton extends ToggleButton {
  constructor(i18n: I18N) {
    super(i18n);
    this.primaryIconName = 'edit-2';
    this.primaryLabel = 'Edit';
    this.secondaryIconName = 'remove-3';
    this.secondaryLabel = 'Cancel';
  }
}
