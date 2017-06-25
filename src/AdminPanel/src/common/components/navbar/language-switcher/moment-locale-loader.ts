export class MomentLocaleLoader {
  load(locale: string): Promise<any> {
    return SystemJS.import(this.getLocaleModule(locale))
      .catch(() => {
        const simplified = this.simplify(locale);
        return SystemJS.import(this.getLocaleModule(simplified));
      });
  }

  private getLocaleModule(locale: string): string {
    return `moment/locale/${locale}`;
  }

  // simplify('en-US') == simplify('en-GB') == simplify('en') == 'en'
  private simplify(locale: string): string {
    return locale.split('-')[0];
  }
}
