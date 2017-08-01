import "bootstrap";
import "arrive";
import "bootstrap-material-design";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";
import {configure as configureHttpClient} from "config/http-client";
import {MetricsCollector} from "common/metrics/metrics-collector";
import {MetricsEventListener} from "common/metrics/metrics-event-listener";
import {CustomValidationRules} from "common/validation/custom-validation-rules";
import {installValidationMessageLocalization} from "common/validation/validation-message-localization";
import {AppRouter} from "aurelia-router";
import {CurrentUserFetcher} from "users/current/current-user-fetcher";
import {Backend} from "aurelia-i18n";
import {Container} from "aurelia-dependency-injection";
import {I18nConfig} from "config/i18n";

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
      i18n.i18next.use(Backend.with(aurelia.loader));
      const config: I18nConfig = Container.instance.get(I18nConfig);
      return i18n.setup({
        backend: {
          loadPath: 'res/locales/{{lng}}/{{lng}}_{{ns}}.json',
        },
        preload: config.getSupportedUILanguages(),
        fallbackLng: ['en'], // CAREFUL! Anything but 'en' here will break English translation because it's missing 99% of terms
        ns: config.namespaces,
        defaultNS: 'generic',
        attributes: ['t'],
        // Since we're translating from literals (eg. "Select language") instead of using keys (eg. "language.add.select"), we need some
        // uncommon namespace and key separators. (Defaults are ':' and '.' which are common in translated literals)
        nsSeparator: '::',
        keySeparator: '//',
        debug: false
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
