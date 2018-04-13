import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {LanguageRepository} from "./language-repository";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class LanguagesList implements ComponentAttached {
  languages: Language[];
  addFormOpened: boolean = false;

  constructor(private languageRepository: LanguageRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer) {
  }

  attached(): void {
    this.languageRepository.getList().then(languages => {
      this.languages = languages;
    });
  }

  addNewLanguage(newLanguage: Language) {
    return this.languageRepository.post(newLanguage).then(language => {
      this.addFormOpened = false;
      this.languages.push(language);
    });
  }

  saveEditedLanguage(language: Language, changedLanguage: Language): Promise<any> {
    const originalLanguage = this.entitySerializer.clone(language);
    $.extend(language, changedLanguage);
    language.pendingRequest = true;
    return this.languageRepository
      .update(changedLanguage)
      .catch(() => $.extend(language, originalLanguage))
      .finally(() => language.pendingRequest = false);
  }

  deleteLanguage(language: Language): Promise<any> {
    return this.deleteEntityConfirmation.confirm('language', language.code)
      .then(() => language.pendingRequest = true)
      .then(() => this.languageRepository.remove(language))
      .then(() => this.languageRepository.getList())
      .then(languages => this.languages = languages)
      .finally(() => language.pendingRequest = false);
  }
}
