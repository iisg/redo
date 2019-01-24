import * as moment from "moment";

export enum DateRangeMode {
  TODAY = 'today',
  YESTERDAY = 'yesterday',
  CURRENT_WEEK = 'current_week',
  LAST_7_DAYS = 'last_7_days',
  PREVIOUS_WEEK = 'previous_week',
  CURRENT_MONTH = 'current_month',
  LAST_30_DAYS = 'last_30_days',
  PREVIOUS_MONTH = 'previous_month',
  PREVIOUS_TWO_MONTHS = 'previous_two_months',
  CURRENT_YEAR = 'current_year',
  LAST_365_DAYS = 'last_365_days',
  PREVIOUS_YEAR = 'previous_year',
}

export class DateRangeConfig {
  mode: DateRangeMode;
  dateFrom: string;
  dateTo: string;

  constructor(private selectedMode: DateRangeMode) {
    this.mode = selectedMode;
    this.setDates();
  }

  formatDate(date): string {
    return moment(date).format("YYYY-MM-DD");
  }

  public static isDateValid(input) {
    return input ? (moment(input, "YYYY-MM-DD", true).isValid()) : false;
  }

  setDates(): void {
    let today = this.formatDate(moment());
    switch (this.mode) {
      case DateRangeMode.TODAY:
        this.dateFrom = today;
        this.dateTo = today;
        break;

      case DateRangeMode.YESTERDAY:
        this.dateFrom = this.formatDate(moment().add(-1, 'days'));
        this.dateTo = this.dateFrom;
        break;

      case DateRangeMode.CURRENT_WEEK:
        this.dateFrom = this.formatDate(moment().startOf('week').toDate());
        this.dateTo = this.formatDate(moment().endOf('week').toDate());
        break;

      case DateRangeMode.LAST_7_DAYS:
        this.dateFrom = this.formatDate(moment().add(-7, 'days'));
        this.dateTo = this.formatDate(moment().add(-1, 'days'));
        break;

      case DateRangeMode.PREVIOUS_WEEK:
        let previousweek = moment().add(-1, 'weeks');
        this.dateFrom = this.formatDate(moment(previousweek).startOf('week').toDate());
        this.dateTo = this.formatDate(moment(previousweek).endOf('week').toDate());
        break;

      case DateRangeMode.CURRENT_MONTH:
        this.dateFrom = this.formatDate(moment().startOf('month').toDate());
        this.dateTo = this.formatDate(moment().endOf('month').toDate());
        break;

      case DateRangeMode.LAST_30_DAYS:
        this.dateFrom = this.formatDate(moment().add(-30, 'days'));
        this.dateTo = this.formatDate(moment().add(-1, 'days'));
        break;

      case DateRangeMode.PREVIOUS_MONTH:
        let previousmonth = moment().add(-1, 'months');
        this.dateFrom = this.formatDate(moment(previousmonth).startOf('month').toDate());
        this.dateTo = this.formatDate(moment(previousmonth).endOf('month').toDate());
        break;

      case DateRangeMode.PREVIOUS_TWO_MONTHS:
        previousmonth = moment().add(-1, 'months');
        let previous2months = moment().add(-2, 'months');
        this.dateFrom = this.formatDate(moment(previous2months).startOf('month').toDate());
        this.dateTo = this.formatDate(moment(previousmonth).endOf('month').toDate());
        break;

      case DateRangeMode.CURRENT_YEAR:
        this.dateFrom = this.formatDate(moment().startOf('year').toDate());
        this.dateTo = this.formatDate(moment().endOf('year').toDate());
        break;

      case DateRangeMode.LAST_365_DAYS:
        this.dateFrom = this.formatDate(moment().add(-365, 'days'));
        this.dateTo = this.formatDate(moment().add(-1, 'days'));
        break;

      case DateRangeMode.PREVIOUS_YEAR:
        let previousyear = moment().add(-1, 'years');
        this.dateFrom = this.formatDate(moment(previousyear).startOf('year').toDate());
        this.dateTo = this.formatDate(moment(previousyear).endOf('year').toDate());
        break;

      default:
        this.dateFrom = undefined;
        this.dateTo = undefined;
    }
  }
}
