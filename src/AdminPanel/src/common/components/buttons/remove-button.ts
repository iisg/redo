import {customElement, useView} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@useView('./toggle-button.html')
@customElement('remove-button')
@autoinject
export class RemoveButton extends ToggleButton {
  constructor(i18n: I18N) {
    super(i18n);
    this.primaryIconName = 'remove-4';
    this.primaryLabel = 'Remove';
  }
}
