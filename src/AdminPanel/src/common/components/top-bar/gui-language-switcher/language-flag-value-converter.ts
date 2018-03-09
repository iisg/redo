import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class LanguageFlagValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(language: string): string {
    return this.i18n.i18next.t('meta//flag', {
      lng: language,
      defaultValue: language || 'dummy'
    });
  }
}
