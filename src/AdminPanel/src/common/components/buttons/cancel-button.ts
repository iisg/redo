import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {customElement, useView} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";

@useView('./toggle-button.html')
@customElement('cancel-button')
@autoinject
export class CancelButton extends ToggleButton {
  constructor(i18n: I18N) {
    super(i18n);
    this.primaryIconName = 'remove-3';
    this.primaryLabel = 'Cancel';
  }
}
