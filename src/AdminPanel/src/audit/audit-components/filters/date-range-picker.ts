import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import "eonasdan-bootstrap-datetimepicker";
import {values} from "lodash";
import * as moment from "moment";
import {ChangeEvent} from "common/events/change-event";
import {changeHandler, twoWay} from "common/components/binding-mode";
import {isString} from "common/utils/object-utils";
import {DateMode, FlexibleDateContent, inputDateConfig} from "resources/controls/input/flexible-date-input/flexible-date-config";
import {DateRangeConfig, DateRangeMode} from "./date-range-config";

enum DateRangePart {
  FROM = 'from',
  TO = 'to',
}

@autoinject
export class DateRangePicker implements ComponentAttached {
  private dateMode: DateMode;
  private rangeDateMode: DateMode;
  private isLoaded = false;
  private optionIsChanging = false;

  predefinedDateRanges: string[] = values(DateRangeMode);
  value: FlexibleDateContent;
  datepicker: Element;
  linkedDatepicker: Element;

  @bindable(changeHandler('updatepredefinedDateRange')) predefinedDateRange;
  @bindable(twoWay.and(changeHandler('updateFromTo'))) dateFrom: string;
  @bindable(twoWay.and(changeHandler('updateFromTo'))) dateTo: string;

  constructor(private element: Element) {
  }

  attached() {
    this.dateMode = DateMode.RANGE;
    this.rangeDateMode = DateMode.DAY;
    this.updateFromTo();
    /**
     * setTimeout due to aurelia lifecycle. Before rendering html there are no ref elements here,
     * so we cannot create datetimepickers. setTimeout delegates creating ones to next lifecycle.
     */
    setTimeout(() => {
      this.createDateTimePickers();
      this.valueChanged();
      this.isLoaded = true;
    });

  }

  private createDateTimePickers() {
    const options = inputDateConfig[this.rangeDateMode].options;
    $(this.datepicker).datetimepicker(options);
    $(this.linkedDatepicker).datetimepicker(options);

    if (this.value) {
      if (DateRangeConfig.isDateValid(this.value.from)) {
        $(this.linkedDatepicker).data("DateTimePicker").minDate(moment(this.value.from));
      }
      if (DateRangeConfig.isDateValid(this.value.to)) {
        $(this.datepicker).data('DateTimePicker').maxDate(moment(this.value.to));
      }
    }
    this.listenForDateRangePickerEvents();
  }

  listenForDateRangePickerEvents() {
    let dateData = new FlexibleDateContent();
    dateData.mode = this.dateMode;
    dateData.rangeMode = this.rangeDateMode;
    $(this.datepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate = e.date;
      $(this.linkedDatepicker).data("DateTimePicker").minDate(e.date);
      dateData.from = inputDate ? inputDate.format() : undefined;
      this.value = dateData;
      this.element.dispatchEvent(ChangeEvent.newInstance());
      this.dateFrom = this.getDatePart(this.value, DateRangePart.FROM);
      this.updateFromTo();
    });
    $(this.linkedDatepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate = e.date;
      $(this.datepicker).data('DateTimePicker').maxDate(e.date);
      dateData.to = inputDate ? inputDate.format() : undefined;
      this.value = dateData;
      this.element.dispatchEvent(ChangeEvent.newInstance());
      this.dateTo = this.getDatePart(this.value, DateRangePart.TO);
      this.updateFromTo();
    });
  }

  valueChanged() {
    if (!this.datepicker || !this.value) {
      return;
    }
    let to, from;
    if (!isString(this.value)) {
      from = this.value.from;
      to = this.value.to;
    }
    $(this.datepicker).data('DateTimePicker').date("");
    $(this.linkedDatepicker).data('DateTimePicker').date("");
    if (from) {
      from = moment(from).format(inputDateConfig[this.rangeDateMode].format);
      $(this.datepicker).data('DateTimePicker').date(from);
    }
    if (to) {
      to = moment(to).format(inputDateConfig[this.rangeDateMode].format);
      $(this.linkedDatepicker).data('DateTimePicker').date(to);
    }
  }

  getDatePart(input: FlexibleDateContent, part: DateRangePart): string {
    if (input && input[part]) {
      let date = moment(input[part]);
      if (part == DateRangePart.FROM) {
        date = date.startOf('day');
      } else {
        date = date.endOf('day');
      }
      return date.format();
    }
    return undefined;
  }

  updateFromTo(): void {
    if (!this.value) {
      this.value = new FlexibleDateContent();
    }
    this.value.from = this.dateFrom;
    this.value.to = this.dateTo;
    if (!this.optionIsChanging) {
      this.predefinedDateRange = undefined;
    }
  }

  setDateRange(dateFrom: string, dateTo: string): void {
    this.dateFrom = dateFrom;
    this.dateTo = dateTo;
    this.updateFromTo();
    this.valueChanged();
  }

  updatepredefinedDateRange(): void {
    if (this.predefinedDateRange && !this.optionIsChanging) {
      this.optionIsChanging = true;
      let dateFrom: string = undefined;
      let dateTo: string = undefined;
      $(this.datepicker).data('DateTimePicker').maxDate(false);
      $(this.linkedDatepicker).data("DateTimePicker").minDate(false);
      $(this.datepicker).data('DateTimePicker').clear();
      $(this.linkedDatepicker).data("DateTimePicker").clear();
      this.setDateRange(dateFrom, dateTo);
      let dateConfig = new DateRangeConfig(this.predefinedDateRange);
      dateFrom = dateConfig.dateFrom;
      dateTo = dateConfig.dateTo;
      this.setDateRange(dateFrom, dateTo);
      this.optionIsChanging = false;
    }
  }
}
