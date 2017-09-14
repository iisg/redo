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
import {CurrentUserFetcher} from "users/current/current-user-fetcher";
import {i18nConfigurator} from "config/i18n";

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
    .plugin('aurelia-i18n', i18nConfigurator(aurelia))
    .plugin('martingust/aurelia-repeat-strategies')
    .globalResources([
      'common/authorization/require-role',
      'common/bootstrap/bootstrap-tooltip',
      'common/bootstrap/hover-aware',
      'common/components/font-awesome/fa',
      'common/components/loading-bar/loading-bar.html',
      'common/components/loading-bar/throbber.html',
      'common/components/disabled-link/disabled-link',
      'resources-config/multilingual-field/multilingual-editor',
      'resources-config/multilingual-field/in-current-language',
      'common/components/promise-button/promise-button',
      'common/components/submit-button/submit-button.html',
      'common/http-client/invalid-command-message.html', // used in alerts by GlobalExceptionInterceptor
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
