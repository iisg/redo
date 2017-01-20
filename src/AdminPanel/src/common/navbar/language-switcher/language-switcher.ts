import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {containerless} from "aurelia-templating";
import {AureliaCookie} from "aurelia-cookie";
import {supportedUILanguages} from "../../../locales/language-constants";
import {LanguageRepository} from "../../../resources-config/language-config/language-repository";
import {Configure} from "aurelia-configuration";

@containerless()
@autoinject
export class LanguageSwitcher {
  private readonly COOKIE_NAME = 'locale';

  languages = [];

  currentLanguage: string;

  constructor(private i18n: I18N, private languageRepository: LanguageRepository, private config: Configure) {
    const initialLanguage = AureliaCookie.get(this.COOKIE_NAME) || this.getDefaultUILanguage();
    if (supportedUILanguages.indexOf(initialLanguage) != -1) {
      this.initialize(initialLanguage);
    } else {
      this.initialize(supportedUILanguages[0]);
      if (initialLanguage == this.getDefaultUILanguage()) {
        const supported = supportedUILanguages.join(', ');
        throw new Error(`Unsupported language '${initialLanguage}' in backend configuration. Supported languages: [${supported}].`);
      }
    }
  }

  private initialize(language: string) {
    this.i18n.setLocale(language);  // set i18n's locale instantly to prevent flash of untranslated content
    this.languageRepository.getList().then(() => { // wait until flags are fetched, then select language properly
      this.languages = supportedUILanguages;
      this.select(language, false);
    });
  }

  private getDefaultUILanguage(): string {
    return this.config.get('default_ui_language');
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
