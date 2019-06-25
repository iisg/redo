import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "common/components/binding-mode";
import {DateMode, inputDateConfig} from "../../../../resources/controls/input/flexible-date-input/flexible-date-config";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class FlexibleDateModeEditor {
  @bindable(twoWay) mode: DateMode;
  @bindable originalMode: DateMode;
  @bindable hasBase: boolean;
  rangeDateModes: DateMode[];

  constructor(private i18n: I18N) {
  }

  attached() {
    this.rangeDateModes = Object.values(DateMode).filter(v => v != DateMode.RANGE);
  }

  resetToOriginalValues() {
    this.mode = this.originalMode;
  }

  @computedFrom('mode', 'originalMode')
  get wasModified(): boolean {
    return this.mode != this.originalMode;
  }

  format = dateMode => inputDateConfig[dateMode]
    ? inputDateConfig[dateMode].format
    : this.i18n.tr('controls::flexible-mode-format');
}
