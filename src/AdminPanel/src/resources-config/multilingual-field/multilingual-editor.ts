import {autoinject} from "aurelia-dependency-injection";
import {Language} from "../language-config/language";
import {LanguageRepository} from "../language-config/language-repository";
import {bindable} from "aurelia-templating";
import {generateId} from "common/utils/string-utils";
import {booleanAttribute} from "common/components/boolean-attribute";
import {twoWay} from "common/components/binding-mode";
import {VoidFunction} from "common/utils/function-utils";

@autoinject
export class MultilingualEditor {
  @bindable label: string;
  @bindable placeholder: Object = {};
  @bindable(twoWay) value: Object = {};
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable onLoaded: VoidFunction = () => {
  }

  languages: Language[];
  fieldId: string = generateId();
  columnClass: string;

  constructor(private languageRepository: LanguageRepository) {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
      this.columnClass = 'col-sm-' + Math.round(12 / Math.min(this.languages.length, 4));
      this.onLoaded();
    });
  }
}
