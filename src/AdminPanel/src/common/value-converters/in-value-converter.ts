export class InValueConverter implements ToViewValueConverter {
  toView(needle: any, haystack: any[]): boolean {
    return haystack.indexOf(needle) !== -1;
  }
}
