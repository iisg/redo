import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {containerless} from "aurelia-templating";
import {AureliaCookie} from "aurelia-cookie";
import {LanguageRepository} from "resources-config/language-config/language-repository";
import {I18nConfig} from "locales/i18n-config";

@containerless()
@autoinject
export class LanguageSwitcher {
  private readonly COOKIE_NAME = 'locale';

  languages = [];

  currentLanguage: string;

  constructor(private i18n: I18N, private languageRepository: LanguageRepository, private config: I18nConfig) {
    const supportedLanguages = this.config.getSupportedUILanguages();
    const initialLanguage = AureliaCookie.get(this.COOKIE_NAME) || this.config.getDefaultUILanguage();
    if (supportedLanguages.indexOf(initialLanguage) != -1) {
      this.initialize(initialLanguage);
    } else {
      this.initialize(supportedLanguages[0]);
      if (initialLanguage == this.config.getDefaultUILanguage()) {
        const supported = supportedLanguages.join(', ');
        throw new Error(`Unsupported language '${initialLanguage}' in backend configuration. Supported languages: [${supported}].`);
      }
    }
  }

  private initialize(language: string) {
    this.i18n.setLocale(language);  // set i18n's locale instantly to prevent flash of untranslated content
    this.languageRepository.getList().then(() => { // wait until flags are fetched, then select language properly
      this.languages = this.config.getSupportedUILanguages();
      this.select(language, false);
    });
  }

  select(languageCode: string, reload: boolean = true) {
    this.currentLanguage = languageCode;
    this.i18n.setLocale(this.currentLanguage);
    this.updateCookie();
    if (reload) {
      window.location.reload(true);
    }
  }

  private updateCookie() {
    AureliaCookie.set(this.COOKIE_NAME, this.i18n.getLocale(), {expiry: -1});
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
