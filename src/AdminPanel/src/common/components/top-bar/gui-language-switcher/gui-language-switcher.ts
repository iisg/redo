import {autoinject} from "aurelia-dependency-injection";
import {containerless} from "aurelia-templating";
import {LanguageRepository} from "resources-config/language-config/language-repository";
import {I18nParams} from "config/i18n";
import {GuiLanguage} from "common/i18n/gui-language";

@containerless()
@autoinject
export class GuiLanguageSwitcher {
  languages: string[];
  currentLanguage: string;

  constructor(private languageRepository: LanguageRepository,
              private i18nParams: I18nParams,
              private guiLanguage: GuiLanguage) {
    this.currentLanguage = guiLanguage.currentLanguage;
    this.ensureLanguageFlagsAreFetched().then(() => {
      this.languages = i18nParams.supportedUiLanguages;
    });
  }

  private ensureLanguageFlagsAreFetched(): Promise<any> {
    return this.languageRepository.getList();
  }

  select(languageCode: string) {
    this.guiLanguage.changeLanguage(languageCode);
  }
}
