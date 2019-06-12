import {automapped, map} from "../../common/dto/decorators";

@automapped
export class StatisticsBucket {
  static NAME = 'StatisticsBucket';

  @map eventName: string;
  @map eventGroup: string;
  @map count: number;
  @map bucket: Date;
  @map bucketLabel: string;
  @map resourceId: number;
  @map resourceLabel: string;
}
