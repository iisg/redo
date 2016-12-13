import {bindable} from "aurelia-templating";

export class MultilingualDisplay {
  @bindable
  value: Object;

  availableValues: Array<any>;

  valueChanged(newValue: Object) {
    this.availableValues = [];
    if (newValue) {
      for (let availableLanguage in newValue) {
        let text = newValue[availableLanguage];
        if (text && text.trim()) {
          this.availableValues.push({
            languageCode: availableLanguage,
            text: text
          });
        }
      }
    }
  }
}
