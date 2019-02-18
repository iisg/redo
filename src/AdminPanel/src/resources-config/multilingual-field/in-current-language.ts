import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {MultilingualText} from "../metadata/metadata";
import {I18nParams} from "config/i18n";

@autoinject
export class InCurrentLanguageValueConverter implements ToViewValueConverter {
  private desiredLanguagesOrder: Array<string>;

  constructor(private i18n: I18N, private i18nParams: I18nParams) {
  }

  toView(value: MultilingualText): string {
    if (typeof value === 'string') {
      return value;
    }
    this.buildDesiredLanguagesOrder();
    if (value) {
      for (let desiredLanguage of this.desiredLanguagesOrder) {
        if (value[desiredLanguage]) {
          return value[desiredLanguage];
        }
      }
    }

    const anyLanguage = value ? Object.keys(value)[0] : undefined;
    return (anyLanguage === undefined) ? '' : value[anyLanguage];
  }

  private buildDesiredLanguagesOrder() {
    if (!this.desiredLanguagesOrder) {
      const currentLanguageCode = this.i18n.getLocale().toUpperCase();
      const supportedLanguages = this.i18nParams.supportedUiLanguages;
      this.desiredLanguagesOrder = supportedLanguages.map(language => language.toUpperCase());
      let currentLanguageIndex = this.desiredLanguagesOrder.indexOf(currentLanguageCode);
      if (currentLanguageIndex > 0) {
        this.desiredLanguagesOrder.splice(currentLanguageIndex, 1);
        this.desiredLanguagesOrder.unshift(currentLanguageCode);
      }
    }
  }
}
