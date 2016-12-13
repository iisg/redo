import {bindable} from "aurelia-templating";
import {LanguageCodeToFlagValueConverter} from "./language-code-to-flag";
import {autoinject} from "aurelia-dependency-injection";
import {LanguageRepository} from "../../resources-config/language-config/language-repository";

@autoinject
export class Flag {
  @bindable code: string;
  @bindable name: string;
  @bindable size: string = 'xs';

  constructor(private codeConverter: LanguageCodeToFlagValueConverter, private languageRepository: LanguageRepository) {
  }

  codeChanged(newValue) {
    if (newValue) {
      this.languageRepository.getList().then(() => {
        this.name = this.codeConverter.toView(newValue);
      });
    }
  }
}
