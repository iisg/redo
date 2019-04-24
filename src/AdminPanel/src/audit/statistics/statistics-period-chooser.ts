import {bindable} from "aurelia-templating";
import {changeHandler, twoWay} from "common/components/binding-mode";
import {DateRangeConfig, statisticsRangeMode} from "../audit-components/filters/date-range-config";

export class StatisticsPeriodChooser {
  @bindable(changeHandler('updateDateOption')) dateOption;
  @bindable(twoWay) dateFrom: string;
  @bindable(twoWay) dateTo: string;
  availableDateOptions: string[] = statisticsRangeMode;
  private optionIsChanging = false;

  setDateRange(dateFrom: string, dateTo: string): void {
    this.dateFrom = dateFrom;
    this.dateTo = dateTo;
  }

  updateDateOption(): void {
    if (this.dateOption && !this.optionIsChanging) {
      this.optionIsChanging = true;
      let dateFrom: string = undefined;
      let dateTo: string = undefined;
      this.setDateRange(dateFrom, dateTo);
      let dateConfig = new DateRangeConfig(this.dateOption);
      dateFrom = dateConfig.dateFrom;
      dateTo = dateConfig.dateTo;
      this.setDateRange(dateFrom, dateTo);
      this.optionIsChanging = false;
    }
  }
}
