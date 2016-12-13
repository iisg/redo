import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {FloatingAddFormController} from "../add-form/floating-add-form";
import {LanguageRepository} from "./language-repository";

@autoinject
export class LanguageList implements ComponentAttached {
  languageList: Language[];
  addFormController: FloatingAddFormController = {};

  constructor(private languageRepository: LanguageRepository) {
  }

  attached(): void {
    this.languageRepository.findAll().then(languages => {
      this.languageList = languages;
    });
  }

  addNewLanguage(newLanguage: Language) {
    return this.languageRepository.addNew(newLanguage).then(language => {
      this.addFormController.hide();
      this.languageList.push(language);
    });
  }
}
