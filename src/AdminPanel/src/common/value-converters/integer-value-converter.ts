export class IntegerValueConverter implements FromViewValueConverter, ToViewValueConverter {
  fromView(str: string): number {
    return (str.length > 0) ? parseInt(str, 10) : 0;
  }

  toView(int: number): string {
    return '' + int;
  }
}
