import {DateMode, FlexibleDateContent} from "resources/controls/input/flexible-date-input/flexible-date-config";
import {values} from "lodash";
import {bindable, ComponentBind} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

export class DatetimeSearch implements ComponentBind {
  @bindable stringifiedValue: string;
  @bindable disabled: boolean;
  @bindable filterableMetadataId: string;
  @bindable fixedRangeMode: DateMode;
  value: FlexibleDateContent = new FlexibleDateContent();
  selectedDateMode: DateMode = DateMode.RANGE;
  rangeDateModes: DateMode[] = values(DateMode).filter(v => v != DateMode.RANGE);
  selectedRangeDateMode: DateMode = DateMode.YEAR;
  modePlace: number = 0;

  @computedFrom('value', 'filterableMetadataId')
  get isDateFilterSet() {
    return this.filterableMetadataId && (this.value['from'] || this.value['to']);
  }

  bind() {
    const dateFilterObject = JSON.parse(this.stringifiedValue);
    let dateData = new FlexibleDateContent();
    dateData.from = dateFilterObject['from'];
    dateData.to = dateFilterObject['to'];
    dateData.rangeMode = dateFilterObject['rangeMode'];
    dateData.mode = DateMode.RANGE;
    this.selectedRangeDateMode = this.fixedRangeMode ? this.fixedRangeMode : dateData.rangeMode;
    const modeChoice = this.rangeDateModes.indexOf(dateData.rangeMode);
    this.modePlace = modeChoice != -1 ? modeChoice : 0;
    this.value = dateData;
  }

  changeMode() {
    this.modePlace = (this.modePlace + 1) % this.rangeDateModes.length;
    this.selectedRangeDateMode = <DateMode> this.rangeDateModes[this.modePlace];
  }
}
