import {automapped, map} from "common/dto/decorators";

@automapped
export class MinMaxValue {
  static NAME = 'MinMaxValue';

  @map min?: number;
  @map max?: number;

  constructor(min?: number, max?: number) {
    this.min = min;
    this.max = max;
  }
}
