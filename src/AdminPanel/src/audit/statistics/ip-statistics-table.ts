import {bindable, ComponentBind} from "aurelia-templating";
import {Statistics} from "./statistics";
import * as moment from "moment";
import {DateMode, inputDateConfig} from "../../resources/controls/input/flexible-date-input/flexible-date-config";

export class IpStatisticsTable implements ComponentBind {
  @bindable statistics: Statistics;

  uniqueDates: string[] = [];
  uniqueClientIps: string[] = [];

  bind() {
    this.statistics.statisticsEntries.forEach(entry => {
      if (this.uniqueDates.indexOf(entry.statMonth) < 0) {
        this.uniqueDates.push(entry.statMonth);
      }
      if (this.uniqueClientIps.indexOf(entry.clientIp) < 0) {
        this.uniqueClientIps.push(entry.clientIp);
      }
    });
  }

  dateToMonth(date: string): string {
    return moment(date).format(inputDateConfig[DateMode.MONTH].format);
  }

  getStatisticEntrySumByDateAndClientIp(clientIp: string, date: string) {
    const entry =  this.statistics.statisticsEntries.filter(entry => (entry.statMonth === date && entry.clientIp === clientIp));
    return entry.length ? entry[0].monthlySum : '-';
  }
}
