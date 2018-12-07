import {DateMode, FlexibleDateContent} from "./flexible-date-config";
import {values} from "lodash";
import {ComponentAttached, bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";

export class FlexibleDateInput implements ComponentAttached {
  @bindable(twoWay) value: FlexibleDateContent | string;
  @bindable disabled: boolean;
  @bindable selectedDateMode: DateMode;
  dateModes: string[] = values(DateMode);
  rangeDateModes: string[] = values(DateMode).filter(v => v != DateMode.RANGE);
  selectedRangeDateMode: DateMode;

  attached(): void {
    if (this.value) {
      this.selectedDateMode = this.value['mode'];
      this.selectedRangeDateMode = this.value['rangeMode'];
    }
  }
}
