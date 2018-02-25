export class MinMaxValue {
  static NAME = 'MinMaxValue';

  min?: number;
  max?: number;

  constructor(min: number, max: number) {
    this.min = min;
    this.max = max;
  }
}
