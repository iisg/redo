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
    this.value = dateData;
  }
}
