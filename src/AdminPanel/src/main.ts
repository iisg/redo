import "bootstrap";
import "arrive";
import "bootstrap-material-design";
import "bootstrap-select";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";
import * as I18nBackend from "i18next-xhr-backend";
import {configure as configureHttpClient} from "./config/http-client";
import {MetricsCollector} from "./common/metrics/metrics-collector";
import {MetricsEventListener} from "./common/metrics/metrics-event-listener";
import {CustomValidationRules} from "./common/validation/custom-validation-rules";
import {installValidationMessageLocalization} from "./common/validation/validation-message-localization";
import {supportedUILanguages, languageNamespaces} from "./locales/language-constants";
import {translationLoader} from "./locales/translation-loader";
import {AppRouter} from "aurelia-router";
import {CurrentUserFetcher} from "./users/current/current-user-fetcher";

MetricsCollector.timeStart("bootstrap");

LogManager.addAppender(new ConsoleAppender());
LogManager.setLevel(LogManager.logLevel.info);

export function configure(aurelia: Aurelia) {
  aurelia.use
    .standardConfiguration()
    .plugin('aurelia-animator-css')
    .plugin('aurelia-configuration', config => config.setDirectory('/api/'))
    .plugin('aurelia-validation')
    .plugin('aurelia-cookie')
    .plugin("oribella-aurelia-sortable")
    .plugin('aurelia-i18n', (i18n) => {
      i18n.i18next.use(I18nBackend);
      return i18n.setup({
        backend: {
          loadPath: 'locales/{{ns}}/{{lng}}.json',
          ajax: translationLoader
        },
        preload: supportedUILanguages(),
        fallbackLng: [supportedUILanguages()[0], 'en'],
        ns: languageNamespaces,
        defaultNS: 'generic',
        attributes: ['t'],
        // Since we're translating from literals (eg. "Select language") instead of using keys (eg. "language.add.select"), we need some
        // uncommon namespace and key separators. (Defaults are ':' and '.' which are common in translated literals)
        nsSeparator: '::',
        keySeparator: '//'
      }).then(() => {
        const router = aurelia.container.get(AppRouter);
        router.transformTitle = title => i18n.tr('nav::' + title);
      });
    })
    .globalResources([
      'common/authorization/require-role',
      'common/bootstrap/bootstrap-tooltip',
      'common/bootstrap/hover-aware',
      'common/components/font-awesome/fa',
      'common/components/loading-bar/loading-bar',
      'resources-config/multilingual-field/multilingual-editor',
      'resources-config/multilingual-field/in-current-language',
      'common/components/promise-button/promise-button'
    ]);

  configureHttpClient(aurelia);
  installValidationMessageLocalization(aurelia);

  aurelia.container.get(CurrentUserFetcher).fetch()
    .then(user => aurelia.container.registerInstance(CurrentUserFetcher.CURRENT_USER_KEY, user))
    .then(() => aurelia.start())
    .then(() => aurelia.container.get(MetricsEventListener).register())
    .then(() => aurelia.container.get(CustomValidationRules).register())
    .then(() => aurelia.setRoot())
    .then(() => MetricsCollector.timeEnd("bootstrap"));
}
