import {bindable, ComponentAttached} from "aurelia-templating";
import {DateMode, FlexibleDateContent, inputDateConfig} from "../flexible-date-input/flexible-date-config";
import {computedFrom} from "aurelia-binding";
import "eonasdan-bootstrap-datetimepicker";
import {twoWay} from "common/components/binding-mode";
import {isString} from "common/utils/object-utils";
import * as moment from "moment";
import {ChangeEvent} from "common/change-event";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DatetimePicker implements ComponentAttached {
  @bindable dateMode: DateMode;
  @bindable rangeDateMode: DateMode;
  @bindable(twoWay) value: FlexibleDateContent | string;
  @bindable disabled: boolean = false;
  @bindable flexible: boolean = false;
  datepicker: Element;
  linkedDatepicker: Element;
  private isLoaded = false;

  constructor(private element: Element) {
  }

  attached() {
    if (!this.flexible) {
      this.dateMode = DateMode.DATE_TIME;
    }
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
    if (this.isRange) {
      $(this.datepicker).datetimepicker(inputDateConfig[this.rangeDateMode].options);
      $(this.linkedDatepicker).datetimepicker(inputDateConfig[this.rangeDateMode].options);
      this.listenForDateRangePickerEvents();
    } else {
      $(this.datepicker).datetimepicker(inputDateConfig[this.dateMode].options);
      this.listenForDatePickerEvents();
    }
  }

  listenForDatePickerEvents() {
    let dateData = new FlexibleDateContent();
    dateData.mode = this.dateMode;
    $(this.datepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate: moment.Moment = e.date;
      if (!this.flexible) {
        this.value = inputDate.format();
      } else {
        dateData.from = inputDate.format();
        dateData.to = inputDate.format();
        this.value = dateData;
      }
      this.element.dispatchEvent(ChangeEvent.newInstance());
    });
  }

  listenForDateRangePickerEvents() {
    let dateData = new FlexibleDateContent();
    dateData.mode = this.dateMode;
    dateData.rangeMode = this.rangeDateMode;
    dateData.from = (this.value && this.flexible) ? this.value['from'] : this.value;
    dateData.to = (this.value && this.flexible) ? this.value['to'] : this.value;
    if (dateData.from) {
      $(this.linkedDatepicker).data("DateTimePicker").minDate(moment(dateData.from).format(inputDateConfig[this.rangeDateMode].format));
    }
    if (dateData.to) {
      $(this.datepicker).data('DateTimePicker').maxDate(moment(dateData.to).format(inputDateConfig[this.rangeDateMode].format));
    }
    $(this.datepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate = e.date;
      $(this.linkedDatepicker).data("DateTimePicker").minDate(inputDate);
      dateData.from = inputDate ? inputDate.format() : undefined;
      this.value = dateData;
      this.element.dispatchEvent(ChangeEvent.newInstance());
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
    });
  }

  valueChanged() {
    if (!this.datepicker || !this.value) {
      return;
    }
    let to, from;
    if ((this.flexible && !isString(this.value)) || !isString(this.value)) {
      from = this.value['from'];
      to = this.value['to'];
    } else {
      to = from = this.value as string;
    }
    if (this.isRange) {
      if (from) {
        from = moment(from).format(inputDateConfig[this.rangeDateMode].format);
        $(this.datepicker).data('DateTimePicker').date(from);
      }
      if (to) {
        to = moment(to).format(inputDateConfig[this.rangeDateMode].format);
        $(this.linkedDatepicker).data('DateTimePicker').date(to);
      }
    } else if (from) {
      from = moment(from).format(inputDateConfig[this.dateMode].format);
      $(this.datepicker).data('DateTimePicker').date(from);
    }
  }

  @computedFrom('dateMode')
  get isRange(): boolean {
    return this.dateMode === DateMode.RANGE;
  }
}
