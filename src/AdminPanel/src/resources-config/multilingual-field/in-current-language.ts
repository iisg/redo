import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {MultilingualText} from "../metadata/metadata";
import {I18nConfig} from "locales/i18n-config";

@autoinject
export class InCurrentLanguageValueConverter implements ToViewValueConverter {
  private desiredLanguagesOrder: Array<string>;

  constructor(private i18n: I18N, private config: I18nConfig) {
  }

  toView(value: MultilingualText): string {
    this.buildDesiredLanguagesOrder();
    if (value) {
      for (let desiredLanguage of this.desiredLanguagesOrder) {
        if (value[desiredLanguage]) {
          return value[desiredLanguage];
        }
      }
    }
    return '';
  }

  private buildDesiredLanguagesOrder() {
    if (!this.desiredLanguagesOrder) {
      const currentLanguageCode = this.i18n.getLocale().toUpperCase();
      const supportedLanguages = this.config.getSupportedUILanguages();
      this.desiredLanguagesOrder = supportedLanguages.map(language => language.toUpperCase());
      let currentLanguageIndex = this.desiredLanguagesOrder.indexOf(currentLanguageCode);
      if (currentLanguageIndex > 0) {
        this.desiredLanguagesOrder.splice(currentLanguageIndex, 1);
        this.desiredLanguagesOrder.unshift(currentLanguageCode);
      }
    }
  }
}
