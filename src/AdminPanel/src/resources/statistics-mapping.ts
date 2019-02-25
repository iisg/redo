import {autoinject} from "aurelia-dependency-injection";
import {maps} from "../common/dto/decorators";
import {ArrayMapper} from "../common/dto/mappers";
import {TypeRegistry} from "../common/dto/registry";
import {StatisticEntry} from "../audit/statistics/statistic-entry";
import {Statistics} from "../audit/statistics/statistics";

@autoinject
@maps('StatisticEntry[]')
export class StatisticEntryMapper extends ArrayMapper<StatisticEntry> {
  constructor(typeRegistry: TypeRegistry) {
    super(typeRegistry.getMapperByType(StatisticEntry.NAME), typeRegistry.getFactoryByType(StatisticEntry.NAME));
  }
}

@autoinject
@maps('Statistics[]')
export class StatisticsMapper extends ArrayMapper<Statistics> {
  constructor(typeRegistry: TypeRegistry) {
    super(typeRegistry.getMapperByType(Statistics.NAME), typeRegistry.getFactoryByType(Statistics.NAME));
  }
}
