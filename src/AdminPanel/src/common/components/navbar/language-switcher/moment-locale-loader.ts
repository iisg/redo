export class MomentLocaleLoader {
  load(locale: string): Promise<any> {
    if (locale = 'en') {
      return Promise.resolve(); // moment's default locale
    }
    return this.loadLocaleModule(locale).catch(() => {
      const simplified = this.simplify(locale);
      return (simplified != locale)
        ? this.loadLocaleModule(simplified).catch(() => Promise.resolve())
        : Promise.resolve();
    });
  }

  private loadLocaleModule(locale: string): Promise<any> {
    return SystemJS.import(this.getLocaleModuleName(locale));
  }

  private getLocaleModuleName(locale: string): string {
    return `moment/locale/${locale}`;
  }

  // simplify('en-US') == simplify('en-GB') == simplify('en') == 'en'
  private simplify(locale: string): string {
    return locale.split('-')[0];
  }
}
