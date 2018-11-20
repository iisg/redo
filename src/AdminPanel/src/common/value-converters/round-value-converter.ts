export class RoundValueConverter implements ToViewValueConverter {
  toView(double: number | string, precision: number): string {
    if (typeof double !== 'number') {
      double = parseFloat(double);
    }
    return double.toFixed(precision);
  }
}
