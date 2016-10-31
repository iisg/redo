import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class LanguageChooser implements ComponentAttached {
  languages: String[];

  @bindable({defaultBindingMode: bindingMode.twoWay})
  language: String;

  constructor(config: Configure) {
    this.languages = config.get('supported_languages');
  }

  attached() {
    this.setCurrentLanguage(this.languages[0]);
  }

  setCurrentLanguage(language: String) {
    this.language = language;
  }
}
