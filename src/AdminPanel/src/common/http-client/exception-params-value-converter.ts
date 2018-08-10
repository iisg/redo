import {InCurrentLanguageValueConverter} from './../../resources-config/multilingual-field/in-current-language';
import {autoinject} from 'aurelia-dependency-injection';
import {mapValuesShallow} from "../utils/object-utils";
import {I18N} from "aurelia-i18n";

@autoinject
export class ExceptionParamsValueConverter {
  constructor(private inCurrentLanguage: InCurrentLanguageValueConverter, private i18n: I18N) {
  }

  toView(params) {
    params = this.translateParams(params);
    return this.selectLabelTranslations(params);
  }

  private selectLabelTranslations(params: AnyMap<any>): AnyMap<string> {
    return mapValuesShallow(params, param => this.selectLabelTranslationCallback(param));
  }

  private selectLabelTranslationsArray(params: any[]): any[] {
    return params.map(param => this.selectLabelTranslationCallback(param));
  }

  private translateParams(params: AnyMap<any>): AnyMap<any> {
    if (params.translateParams) {
      for (let translateParam of params.translateParams) {
        params[translateParam] = this.i18n.tr('exceptions::' + params[translateParam]);
      }
    }
    return params;
  }

  private selectLabelTranslationCallback(value: any): string {
    if (value && value.hasOwnProperty('label')) {
      return '`' + this.inCurrentLanguage.toView(value['label']) + '`';
    } else if (value instanceof Array) {
      return this.selectLabelTranslationsArray(value).join(', ');
    } else if (value instanceof Object) {
      return JSON.stringify(this.selectLabelTranslations(value));
    }
    return value;
  }
}