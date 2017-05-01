import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {LanguageRepository} from "./language-repository";
import {deepCopy} from "common/utils/object-utils";

@autoinject
export class LanguageList implements ComponentAttached {
  languageList: Language[];
  addFormOpened: boolean = false;

  constructor(private languageRepository: LanguageRepository) {
  }

  attached(): void {
    this.languageRepository.getList().then(languages => {
      this.languageList = languages;
    });
  }

  addNewLanguage(newLanguage: Language) {
    return this.languageRepository.post(newLanguage).then(language => {
      this.addFormOpened = false;
      this.languageList.push(language);
    });
  }

  saveEditedLanguage(language: Language, changedLanguage: Language): Promise<Language> {
    const originalLanguage = deepCopy(language);
    $.extend(language, changedLanguage);
    return this.languageRepository.update(changedLanguage).catch(() => $.extend(language, originalLanguage));
  }
}
