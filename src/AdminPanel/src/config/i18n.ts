import {autoinject} from "aurelia-dependency-injection";
import {Configure} from "aurelia-configuration";
import {Aurelia} from "aurelia-framework";
import {I18N, Backend} from "aurelia-i18n";
import {AppRouter} from "aurelia-router";

export function i18nConfigurator(aurelia: Aurelia) {
  return (i18n: I18N) => {
    i18n.i18next.use(Backend.with(aurelia.loader));
    const config: I18nParams = aurelia.container.get(I18nParams);
    return i18n.setup({
      backend: {
        loadPath: 'res/locales/{{lng}}/{{lng}}_{{ns}}.json',
      },
      preload: config.supportedUiLanguages,
      fallbackLng: ['en'], // CAREFUL! Anything but 'en' here will break English translation because it's missing 99% of terms
      ns: config.namespaces,
      defaultNS: 'generic',
      attributes: ['t'],
      // Since we're translating from literals (eg. "Select language") instead of using keys (eg. "language.add.select"), we need some
      // uncommon namespace and key separators. (Defaults are ':' and '.' which are common in translated literals)
      nsSeparator: '::',
      keySeparator: '//',
      interpolation: {
        format: (value: string, format: string, language: string) => {
          format = format.toLowerCase();
          if (format == 'capitalized') {
            return value.charAt(0).toUpperCase() + value.slice(1);
          }
        }
      },
      debug: false
    }).then(() => {
      const router = aurelia.container.get(AppRouter);
      router.transformTitle = title => i18n.tr('nav::' + title);
    });
  };
}

@autoinject
export class I18nParams {
  readonly namespaces = ['generic', 'validation', 'nav', 'controls', 'exceptions', 'roles',
    'system_metadata', 'entity_types', 'resource_classes', 'metadata_constraints'];

  constructor(private config: Configure) {
  }

  get supportedUiLanguages(): string[] {
    return this.config.get('supported_ui_languages');
  }

  get defaultUiLanguage(): string {
    return this.config.get('default_ui_language');
  }
}
