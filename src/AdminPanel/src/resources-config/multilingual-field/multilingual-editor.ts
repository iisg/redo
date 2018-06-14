import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";
import {VoidFunction} from "common/utils/function-utils";
import {generateId} from "common/utils/string-utils";
import {Language} from "../language-config/language";
import {LanguageRepository} from "../language-config/language-repository";

@autoinject
export class MultilingualEditor {
  @bindable label: string;
  @bindable placeholder: Object = {};
  @bindable(twoWay) value: Object = {};
  @bindable disabled: boolean;
  @bindable onLoaded: VoidFunction = () => {
  }
  @bindable @booleanAttribute required: boolean;

  languages: Language[];
  fieldId: string = generateId();

  constructor(private languageRepository: LanguageRepository) {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
      this.onLoaded();
    });
  }
}
