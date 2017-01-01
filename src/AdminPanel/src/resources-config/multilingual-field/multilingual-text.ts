import {autoinject} from "aurelia-dependency-injection";
import {Language} from "../language-config/language";
import {LanguageRepository} from "../language-config/language-repository";
import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {generateId} from "../../common/utils/string-utils";

@autoinject
export class MultilingualText {
  languages: Language[];

  fieldId: string = generateId();

  @bindable
  label: string;

  @bindable
  placeholder: Object = {};

  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: Object = {};

  columnClass: string;

  constructor(private languageRepository: LanguageRepository) {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
      this.columnClass = 'col-sm-' + Math.round(12 / Math.min(this.languages.length, 4));
    });
  }
}
