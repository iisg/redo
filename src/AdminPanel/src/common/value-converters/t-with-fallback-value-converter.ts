import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class TWithFallbackValueConverter implements ToViewValueConverter {

  constructor(private i18n: I18N) {
  }

  toView(text: string): string {
    const translated = this.i18n.tr(text);
    return translated.indexOf('//') > 0 ? translated.substr(translated.lastIndexOf('//') + 2) : translated;
  }
}
