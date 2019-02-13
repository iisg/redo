import {bindable, ComponentAttached} from "aurelia-templating";
import {ATOM_DATEFORMAT, DateMode, FlexibleDateContent, inputDateConfig} from "../flexible-date-input/flexible-date-config";
import {computedFrom} from "aurelia-binding";
import "eonasdan-bootstrap-datetimepicker";
import {twoWay} from "common/components/binding-mode";
import {isString} from "common/utils/object-utils";
import * as moment from "moment";
import {ChangeEvent} from "common/events/change-event";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DatetimePicker implements ComponentAttached {
  @bindable dateMode: DateMode;
  @bindable rangeDateMode: DateMode;
  @bindable(twoWay) value: FlexibleDateContent | string;
  @bindable disabled: boolean = false;
  @bindable flexible: boolean = false;
  @bindable onlyRange;
  datepicker: Element;
  fromDatepicker: Element;
  toDatepicker: Element;
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
      this.updateDateTimePickerValues();
      this.isLoaded = true;
    });
  }

  dateModeChanged(newValue, oldValue) {
    if (oldValue) {
      this.updateDateTimePickers();
    }
  }

  rangeDateModeChanged(newValue, oldValue) {
    if (this.rangeDateMode && oldValue) {
      this.updateDateTimePickers();
    }
  }

  updateDateTimePickers() {
    this.changeDateTimePickersFormat();
    this.updateDateTimePickerValues();
  }

  private createDateTimePickers() {
    const dateMode = this.rangeDateMode || this.dateMode || DateMode.YEAR;
    if (this.flexible) {
      $(this.fromDatepicker).datetimepicker(inputDateConfig[dateMode].options);
      $(this.toDatepicker).datetimepicker(inputDateConfig[dateMode].options);
      this.listenForDateRangePickerEvents();
    }
    if (!this.onlyRange) {
      $(this.datepicker).datetimepicker(inputDateConfig[dateMode].options);
      this.listenForDatePickerEvents();
    }
  }

  private changeDateTimePickersFormat() {
    if (this.isRange && this.flexible) {
      $(this.fromDatepicker).data('DateTimePicker').options(inputDateConfig[this.rangeDateMode].options);
      $(this.toDatepicker).data('DateTimePicker').options(inputDateConfig[this.rangeDateMode].options);
    } else if (!this.onlyRange) {
      $(this.datepicker).data('DateTimePicker').options(inputDateConfig[this.dateMode].options);
    }
    this.value = this.createCurrentFlexibleDateValue();
  }

  listenForDatePickerEvents() {
    $(this.datepicker).datetimepicker().on('dp.change', e => {
      const dateData = new FlexibleDateContent();
      dateData.mode = this.dateMode;
      if (!this.isLoaded) {
        return;
      }
      const inputDate: moment.Moment = e.date;
      if (inputDate) {
        if (!this.flexible) {
          this.value = inputDate.format();
        } else {
          if (inputDate instanceof FlexibleDateContent) {
            dateData.from = inputDate.from;
            dateData.to = inputDate.to;
          } else {
            dateData.from = inputDate.format();
            dateData.to = inputDate.format();
          }
          this.value = dateData;
        }
      } else {
        this.value = undefined;
      }
      this.element.dispatchEvent(ChangeEvent.newInstance());
    });
  }

  listenForDateRangePickerEvents() {
    let dateData = this.createCurrentFlexibleDateValue();
    const rangeDateMode = this.rangeDateMode || this.dateMode || DateMode.YEAR;
    if (dateData.from) {
      $(this.toDatepicker).data("DateTimePicker").minDate(moment(dateData.from).format(inputDateConfig[rangeDateMode].format));
    }
    if (dateData.to) {
      $(this.fromDatepicker).data('DateTimePicker').maxDate(moment(dateData.to).format(inputDateConfig[rangeDateMode].format));
    }
    $(this.fromDatepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate = e.date;
      $(this.toDatepicker).data("DateTimePicker").minDate(inputDate);
      let dateData = this.createCurrentFlexibleDateValue();
      dateData.from = inputDate ? inputDate.format() : undefined;
      this.value = dateData.from || dateData.to ? dateData : undefined;
      this.element.dispatchEvent(ChangeEvent.newInstance());
    });
    $(this.toDatepicker).on('dp.change', e => {
      if (!this.isLoaded) {
        return;
      }
      const inputDate = e.date;
      $(this.fromDatepicker).data('DateTimePicker').maxDate(e.date);
      let dateData = this.createCurrentFlexibleDateValue();
      dateData.to = inputDate ? inputDate.format() : undefined;
      this.value = dateData.from || dateData.to ? dateData : undefined;
      this.element.dispatchEvent(ChangeEvent.newInstance());
    });
  }

  updateDateTimePickerValues() {
    if (!this.value) {
      return;
    }
    let to, from;
    if ((this.flexible && !isString(this.value)) || !isString(this.value)) {
      from = this.value['from'];
      to = this.value['to'];
    } else {
      to = from = this.value as string;
    }
    if (this.isRange && this.flexible) {
      if (from) {
        from = moment(from, ATOM_DATEFORMAT).toDate();
        $(this.fromDatepicker).data('DateTimePicker').date(from);
      }
      if (to) {
        to = moment(to, ATOM_DATEFORMAT).toDate();
        $(this.toDatepicker).data('DateTimePicker').date(to);
      }
    } else if (from && !this.onlyRange) {
      from = moment(from, ATOM_DATEFORMAT).toDate();
      $(this.datepicker).data('DateTimePicker').date(from);
    }
  }

  @computedFrom('dateMode')
  get isRange(): boolean {
    return this.dateMode === DateMode.RANGE;
  }

  private createCurrentFlexibleDateValue(): FlexibleDateContent {
    let dateData = new FlexibleDateContent();
    dateData.mode = this.dateMode;
    dateData.rangeMode = this.rangeDateMode;
    dateData.from = (this.value && this.flexible) ? this.value['from'] : this.value;
    dateData.to = (this.value && this.flexible) ? this.value['to'] : this.value;
    return dateData;
  }
}
