export class UnderscoresToHyphensValueConverter implements ToViewValueConverter {
  toView(value: string): string {
    return (value || '').replace(/_/g, '-');
  }
}
