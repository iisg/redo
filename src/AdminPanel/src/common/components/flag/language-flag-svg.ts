export class LanguageFlagSvgValueConverter implements ToViewValueConverter {
  toView(flag: string): string {
    if (flag) {
      return `/jspm_packages/npm/region-flags@1.1.0/svg/${flag.toUpperCase()}.svg`;
    } else {
      return '/files/dummy-flag.svg';
    }
  }
}
