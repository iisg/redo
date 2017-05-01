import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {MultilingualTextType} from "../metadata/metadata";
import {supportedUILanguages} from "locales/language-constants";

@autoinject
export class InCurrentLanguageValueConverter implements ToViewValueConverter {
  private desiredLanguagesOrder: Array<string>;

  constructor(private i18n: I18N) {
  }

  toView(value: MultilingualTextType): string {
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
      let currentLanguageCode = this.i18n.getLocale().toUpperCase();
      this.desiredLanguagesOrder = supportedUILanguages().map(language => language.toUpperCase());
      let currentLanguageIndex = this.desiredLanguagesOrder.indexOf(currentLanguageCode);
      if (currentLanguageIndex > 0) {
        this.desiredLanguagesOrder.splice(currentLanguageIndex, 1);
        this.desiredLanguagesOrder.unshift(currentLanguageCode);
      }
    }
  }
}
