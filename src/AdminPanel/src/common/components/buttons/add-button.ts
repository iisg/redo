import {customElement, useView} from "aurelia-templating";
import {ToggleButton} from "./toggle-button";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@useView('./toggle-button.html')
@customElement('add-button')
@autoinject()
export class AddButton extends ToggleButton {
  constructor(i18n: I18N) {
    super(i18n);
    this.primaryIconName = 'add-resource-3';
    this.primaryLabel = 'Add';
    this.secondaryIconName = 'remove-3';
    this.secondaryLabel = 'Cancel';
    this.onClick = () => {
      this.toggled = !this.toggled;
    };
  }
}
