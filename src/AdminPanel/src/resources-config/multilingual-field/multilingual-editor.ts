import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {VoidFunction} from "common/utils/function-utils";
import {generateId} from "common/utils/string-utils";
import {Language} from "../language-config/language";
import {LanguageRepository} from "../language-config/language-repository";

@autoinject
export class MultilingualEditor {
  @bindable placeholder: Object = {};
  @bindable(twoWay) value: Object = {};
  @bindable disabled: boolean;
  @bindable onLoaded: VoidFunction = () => {
  };

  languages: Language[];
  fieldId: string = generateId();

  constructor(private languageRepository: LanguageRepository) {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
      this.onLoaded();
    });
  }
}
