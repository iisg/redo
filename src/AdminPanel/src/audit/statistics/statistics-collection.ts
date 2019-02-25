import {automapped, map} from "../../common/dto/decorators";
import {Statistics} from "./statistics";

@automapped
export class StatisticsCollection {
  static NAME = 'StatisticsCollection';

  @map resourcesCount: number;
  @map openResourcesCount: number;
  @map('Statistics[]') statistics: Statistics[] = [];
}
