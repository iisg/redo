import {InCurrentLanguageValueConverter} from './../../resources-config/multilingual-field/in-current-language';
import {autoinject} from 'aurelia-dependency-injection';
import {mapValuesShallow} from "../utils/object-utils";

@autoinject
export class ExceptionParamsValueConverter {
  constructor(private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(params) {
    return this.selectLabelTranslations(params);
  }

  private selectLabelTranslations(params: AnyMap<any>): AnyMap<string> {
    return mapValuesShallow(params, param => this.selectLabelTranslationCallback(param));
  }

  private selectLabelTranslationsArray(params: any[]): any[] {
    return params.map(param => this.selectLabelTranslationCallback(param));
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