import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {LanguageRepository} from "../../../../resources-config/language-config/language-repository";
import {twoWay} from "../../../../common/components/binding-mode";
import {booleanAttribute} from "../../../../common/components/boolean-attribute";

@autoinject
export class SystemLanguagePicker implements ComponentAttached {
  @bindable(twoWay) value: string;
  @bindable @booleanAttribute disabled: boolean = false;
  languageCodes: string[];

  constructor(private languageRepository: LanguageRepository) {
  }

  attached() {
    this.languageRepository.getList().then(languages => {
      this.languageCodes = languages.map(language => language.code);
    });
  }
}
