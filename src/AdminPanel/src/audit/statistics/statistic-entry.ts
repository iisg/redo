import {automapped, map} from "../../common/dto/decorators";

@automapped
export class StatisticEntry {
  static NAME = 'StatisticEntry';

  @map clientIp: string;
  @map monthlySum: number;
  @map statMonth: string;
  @map usageKey: string;
}
