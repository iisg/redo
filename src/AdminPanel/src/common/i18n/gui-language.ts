import {autoinject} from "aurelia-dependency-injection";
import {I18nParams} from "config/i18n";
import {MomentLocaleLoader} from "./moment-locale-loader";
import {AureliaCookie} from "aurelia-cookie";
import {I18N} from "aurelia-i18n";
import * as moment from "moment";
import "moment/locale/pl";

@autoinject
export class GuiLanguage {
  private readonly COOKIE_NAME = 'locale';

  private language: string;

  constructor(private i18nParams: I18nParams, private momentLocaleLoader: MomentLocaleLoader, private i18n: I18N) {
  }

  apply(): void {
    const supportedLanguages = this.i18nParams.supportedUiLanguages;
    const initialLanguage = AureliaCookie.get(this.COOKIE_NAME) || this.i18nParams.defaultUiLanguage;
    if (supportedLanguages.indexOf(initialLanguage) != -1) {
      this.setLanguage(initialLanguage);
    } else {
      this.setLanguage(supportedLanguages[0]);
      if (initialLanguage == this.i18nParams.defaultUiLanguage) {
        const supported = supportedLanguages.join(', ');
        throw new Error(`Unsupported language '${initialLanguage}' in backend configuration. Supported languages: [${supported}].`);
      }
    }
  }

  private setLanguage(language: string) {
    this.language = language;
    this.setCookie(language);
    this.i18n.setLocale(language);
    this.momentLocaleLoader.load(language).then(() => {
      moment.locale(language);
    });
  }

  private setCookie(language: string): void {
    AureliaCookie.set(this.COOKIE_NAME, language, {path: '/admin'});
  }

  get currentLanguage(): string {
    return this.language;
  }

  changeLanguage(newLanguage: string): void {
    this.setCookie(newLanguage);
    window.location.reload();
  }
}
