import {InCurrentLanguageValueConverter} from "./in-current-language";
import {I18N} from "aurelia-i18n";
import {I18nParams} from "../../config/i18n";

describe(InCurrentLanguageValueConverter.name, () => {
  let i18n: I18N;
  let inCurrentLanguage: InCurrentLanguageValueConverter;

  beforeEach(() => {
    i18n = new I18N();
    const config = {supportedUiLanguages: ['PL', 'EN', 'RU']} as I18nParams;
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

  it("returns any value if no locale found", () => {
    spyOn(i18n, 'getLocale').and.returnValue('EN');
    let translated = inCurrentLanguage.toView({'XYZ': 'xyzzy'});
    expect(translated).toEqual('xyzzy');
  });

  it("returns empty string if no locale found and no values provided", () => {
    spyOn(i18n, 'getLocale').and.returnValue('EN');
    let translated = inCurrentLanguage.toView({});
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
