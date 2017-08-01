import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {containerless} from "aurelia-templating";
import {AureliaCookie} from "aurelia-cookie";
import {LanguageRepository} from "resources-config/language-config/language-repository";
import {I18nConfig} from "config/i18n";
import {MomentLocaleLoader} from "./moment-locale-loader";
import * as moment from "moment";

@containerless()
@autoinject
export class LanguageSwitcher {
  private readonly COOKIE_NAME = 'locale';

  languages = [];

  currentLanguage: string;

  constructor(private i18n: I18N,
              private languageRepository: LanguageRepository,
              private i18nConfig: I18nConfig,
              private momentLocaleLoader: MomentLocaleLoader) {
    const supportedLanguages = this.i18nConfig.getSupportedUILanguages();
    const initialLanguage = AureliaCookie.get(this.COOKIE_NAME) || this.i18nConfig.getDefaultUILanguage();
    if (supportedLanguages.indexOf(initialLanguage) != -1) {
      this.initialize(initialLanguage);
    } else {
      this.initialize(supportedLanguages[0]);
      if (initialLanguage == this.i18nConfig.getDefaultUILanguage()) {
        const supported = supportedLanguages.join(', ');
        throw new Error(`Unsupported language '${initialLanguage}' in backend configuration. Supported languages: [${supported}].`);
      }
    }
  }

  private initialize(language: string) {
    this.i18n.setLocale(language);  // set i18n's locale instantly to prevent flash of untranslated content
    const flagsPromise = this.languageRepository.getList();
    const momentLocalePromise = this.momentLocaleLoader.load(language);
    Promise.all([flagsPromise, momentLocalePromise]).then(() => {
      this.languages = this.i18nConfig.getSupportedUILanguages();
      this.select(language, false);
    });
  }

  select(languageCode: string, reload: boolean = true) {
    this.currentLanguage = languageCode;
    this.i18n.setLocale(this.currentLanguage);
    moment.updateLocale(languageCode, {});
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
