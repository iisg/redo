import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {LanguageRepository} from "./language-repository";
import {clearCachedResponse} from "common/repository/cached-response";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class LanguageList implements ComponentAttached {
  languageList: Language[];
  addFormOpened: boolean = false;

  constructor(private languageRepository: LanguageRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer) {
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
      .then(() => {
        clearCachedResponse(this.languageRepository.getList);
        return this.languageRepository.getList();
      })
      .then(languages => this.languageList = languages)
      .finally(() => language.pendingRequest = false);
  }
}
