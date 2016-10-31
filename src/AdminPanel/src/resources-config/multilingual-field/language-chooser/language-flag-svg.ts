export class LanguageFlagSvgValueConverter implements ToViewValueConverter {
  private static readonly LANGUAGE_FLAG_MAP = {
    EN: "GB"
  };

  toView(value: string): string {
    let flag = LanguageFlagSvgValueConverter.LANGUAGE_FLAG_MAP[value] || value;
    return `/jspm_packages/github/behdad/region-flags@1.0.1/svg/${flag}.svg`;
  }
}
