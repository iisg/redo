import {bindable} from "aurelia-templating";
import * as moment from "moment";
import {DateMode, inputDateConfig} from "../../resources/controls/input/flexible-date-input/flexible-date-config";
import {Statistics} from "./statistics";

export class StatisticsTable {
  @bindable statistics: Statistics;

  dateToMonth(date: string): string {
    return moment(date).format(inputDateConfig[DateMode.MONTH].format);
  }
}
