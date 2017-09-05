import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {LanguageRepository} from "./language-repository";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class FlagsSelect implements ComponentAttached {
  @bindable(twoWay) value: string;

  availableFlags: string[];

  dropdown: Element;

  constructor(private languageRepository: LanguageRepository) {
  }

  attached() {
    this.languageRepository.getAvailableFlags().then(availableFlags => this.availableFlags = availableFlags);
  }
}
