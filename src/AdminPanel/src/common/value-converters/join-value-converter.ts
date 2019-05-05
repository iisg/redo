export class JoinValueConverter implements ToViewValueConverter {
  toView(haystack: any[], delimiter: string = ', '): string {
    return haystack.join(delimiter);
  }
}
