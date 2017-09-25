import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
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

@autoinject
export class LanguageFlagValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(language: string): string {
    return this.i18n.i18next.t('meta//flag', {
      lng: language,
      defaultValue: language || 'dummy'
    });
  }
}
