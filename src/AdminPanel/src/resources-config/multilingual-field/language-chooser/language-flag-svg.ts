export class LanguageFlagSvgValueConverter implements ToViewValueConverter {
  toView(flag: string): string {
    if (flag) {
      return `/jspm_packages/github/behdad/region-flags@1.0.1/svg/${flag}.svg`;
    } else {
      return '/files/dummy-flag.svg';
    }
  }
}
