import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Language} from "./language";
import {LanguageRepository} from "./language-repository";
import {deepCopy} from "common/utils/object-utils";
import {clearCachedResponse} from "../../common/repository/cached-response";
import {DeleteEntityConfirmation} from "../../common/dialog/delete-entity-confirmation";
import {setPendingRequest, clearPendingRequest} from "../../common/entity/entity";

@autoinject
export class LanguageList implements ComponentAttached {
  languageList: Language[];
  addFormOpened: boolean = false;

  constructor(private languageRepository: LanguageRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation) {
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
    language.pendingRequest = true;
    return this.languageRepository
      .update(changedLanguage)
      .catch(() => $.extend(language, originalLanguage))
      .finally(clearPendingRequest(language));
  }

  deleteLanguage(language: Language) {
    return this.deleteEntityConfirmation.confirm('language', language.code)
      .then(setPendingRequest(language))
      .then(() => this.languageRepository.remove(language))
      .then(() => {
        clearCachedResponse(this.languageRepository.getList);
        return this.languageRepository.getList();
      })
      .then(languages => this.languageList = languages)
      .finally(clearPendingRequest(language));
  }
}
