import {InCurrentLanguageValueConverter} from "./in-current-language";
import {I18N} from "aurelia-i18n";
import {I18nConfig} from "../../locales/i18n-config";

describe(InCurrentLanguageValueConverter.name, () => {
  let i18n: I18N;
  let inCurrentLanguage: InCurrentLanguageValueConverter;

  beforeEach(() => {
    i18n = new I18N();
    const config = new I18nConfig(undefined);
    config.getSupportedUILanguages = () => ['PL', 'EN', 'RU'];
    inCurrentLanguage = new InCurrentLanguageValueConverter(i18n, config);
  });

  it("returns default polish translation", () => {
    spyOn(i18n, 'getLocale').and.returnValue('PL');
    let translated = inCurrentLanguage.toView({'PL': 'polski', 'EN': 'angielski'});
    expect(translated).toEqual('polski');
  });

  it("returns english translation", () => {
    spyOn(i18n, 'getLocale').and.returnValue('EN');
    let translated = inCurrentLanguage.toView({'PL': 'polski', 'EN': 'angielski'});
    expect(translated).toEqual('angielski');
  });

  it("supports lowercase locale", () => {
    spyOn(i18n, 'getLocale').and.returnValue('en');
    let translated = inCurrentLanguage.toView({'PL': 'polski', 'EN': 'angielski'});
    expect(translated).toEqual('angielski');
  });

  it("returns empty string if no locale found", () => {
    spyOn(i18n, 'getLocale').and.returnValue('EN');
    let translated = inCurrentLanguage.toView({'XYZ': 'polski'});
    expect(translated).toEqual('');
  });

  it("returns other language if available", () => {
    spyOn(i18n, 'getLocale').and.returnValue('PL');
    let translated = inCurrentLanguage.toView({'EN': 'angielski'});
    expect(translated).toEqual('angielski');
  });

  it("returns language in preferred order", () => {
    spyOn(i18n, 'getLocale').and.returnValue('EN');
    let translated = inCurrentLanguage.toView({'PL': 'polski', 'RU': 'ruski'});
    expect(translated).toEqual('polski');
  });
});
