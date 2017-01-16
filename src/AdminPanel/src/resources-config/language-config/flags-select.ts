import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {LanguageRepository} from "./language-repository";

@autoinject
export class FlagsSelect implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: string;

  availableFlags: string[];

  dropdown: Element;

  constructor(private languageRepository: LanguageRepository) {
  }

  attached() {
    this.languageRepository.getAvailableFlags().then(availableFlags => this.availableFlags = availableFlags);
  }
}
