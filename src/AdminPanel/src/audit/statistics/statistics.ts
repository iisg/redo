import {automapped, map} from "../../common/dto/decorators";
import {StatisticEntry} from "./statistic-entry";

@automapped
export class Statistics {
  static NAME = 'Statistics';

  @map usageKey: string;
  @map('StatisticEntry[]') statisticsEntries: StatisticEntry[] = [];
}
