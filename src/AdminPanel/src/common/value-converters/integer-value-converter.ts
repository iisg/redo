export class IntegerValueConverter implements FromViewValueConverter, ToViewValueConverter {
  fromView(str: string): number {
    return parseInt(str, 10);
  }

  toView(int: number): string {
    return int + '';
  }
}
