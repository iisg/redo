import {DateMode, FlexibleDateContent} from "./flexible-date-config";
import {values} from "lodash";
import {bindable, ComponentAttached} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";

export class FlexibleDateInput implements ComponentAttached {
  @bindable(twoWay) value: FlexibleDateContent | string;
  @bindable disabled: boolean;
  @bindable isRangeSelected: boolean;
  @bindable onlyRange;
  @bindable fixedRangeMode: DateMode;
  private isLoaded = false;
  selectedDateMode: DateMode;
  selectedRangeDateMode: DateMode;
  dateMode: DateMode;
  rangeDateModes: DateMode[] = values(DateMode).filter(v => v != DateMode.RANGE);
  modePlace: number = 0;

  attached(): void {
    if (this.fixedRangeMode) {
      this.selectedDateMode = DateMode.RANGE;
      this.selectedRangeDateMode = this.fixedRangeMode;
      this.isRangeSelected = true;
    } else {
      if (this.value) {
        this.isRangeSelected = this.value['mode'] == DateMode.RANGE;
        this.selectedDateMode = this.value['mode'];
        this.selectedRangeDateMode = this.value['rangeMode'];
        this.dateMode = this.isRangeSelected ? this.selectedRangeDateMode : this.selectedDateMode;
      } else if (this.onlyRange) {
        this.selectedDateMode = DateMode.RANGE;
        this.dateMode = this.selectedRangeDateMode = DateMode.YEAR;
        this.isRangeSelected = true;
      } else {
        this.dateMode = this.selectedDateMode = DateMode.YEAR;
        this.isRangeSelected = false;
      }
      const modeChoice = this.rangeDateModes.indexOf(this.dateMode);
      this.modePlace = modeChoice != -1 ? modeChoice : 0;
    }
    this.isLoaded = true;
  }

  isRangeSelectedChanged(newValue, oldValue) {
    if (oldValue != undefined) {
      if (this.isRangeSelected) {
        this.selectedRangeDateMode = this.dateMode;
        this.selectedDateMode = DateMode.RANGE;
      } else {
        this.selectedRangeDateMode = undefined;
        this.selectedDateMode = this.dateMode;
      }
    }
  }

  changeMode() {
    this.modePlace = (this.modePlace + 1) % this.rangeDateModes.length;
    this.dateMode = <DateMode>this.rangeDateModes[this.modePlace];
    if (this.isRangeSelected) {
      this.selectedRangeDateMode = this.dateMode;
    } else {
      this.selectedDateMode = this.dateMode;
    }
  }
}
