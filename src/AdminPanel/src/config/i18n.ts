import {autoinject} from "aurelia-dependency-injection";
import {Configure} from "aurelia-configuration";

@autoinject
export class I18nConfig {
  readonly namespaces = ['generic', 'validation', 'nav', 'controls', 'exceptions', 'roles'];

  constructor (private config: Configure) {
  }

  getSupportedUILanguages(): string[] {
    return this.config.get('supported_ui_languages');
  }

  getDefaultUILanguage(): string {
    return this.config.get('default_ui_language');
  }
}
