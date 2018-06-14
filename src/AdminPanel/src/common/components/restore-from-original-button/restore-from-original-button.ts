import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {customElement, useView} from "aurelia-templating";
import {ToggleButton} from "common/components/buttons/toggle-button";

@useView('common/components/buttons/toggle-button.html')
@customElement('restore-from-original-button')
@autoinject
export class RestoreFromOriginalButton extends ToggleButton {
  constructor(i18n: I18N) {
    super(i18n);
    this.primaryIconName = 'undo-2';
    this.primaryLabel = 'Clear overridden value';
  }
}
