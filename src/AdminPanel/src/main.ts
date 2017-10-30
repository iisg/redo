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
import {GuiLanguage} from "./common/i18n/gui-language";
import {Configure} from "aurelia-configuration";
import {Language} from "./resources-config/language-config/language";
import {Metadata} from "./resources-config/metadata/metadata";
import {ResourceKind} from "./resources-config/resource-kind/resource-kind";
import {Resource} from "./resources/resource";
import {User} from "./users/user";
import {UserRole} from "./users/roles/user-role";
import {Workflow} from "./workflows/workflow";

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
      'common/components/loading-bar/pending-throbber.html',
      'common/components/disabled-link/disabled-link',
      'resources-config/multilingual-field/multilingual-editor',
      'resources-config/multilingual-field/in-current-language',
      'common/components/promise-button/promise-button',
      'common/components/submit-button/submit-button.html',
      'common/http-client/invalid-command-message.html', // used in alerts by GlobalExceptionInterceptor
    ]);

  preloadEntityTypes();
  configureHttpClient(aurelia);
  installValidationMessageLocalization(aurelia);

  aurelia.container.get(CurrentUserFetcher).fetch()
    .then(user => aurelia.container.registerInstance(CurrentUserFetcher.CURRENT_USER_KEY, user))
    .then(() => aurelia.start())
    .then(() => onAureliaStarted(aurelia))
    .then(() => aurelia.setRoot())
    .then(() => MetricsCollector.timeEnd("bootstrap"));
}

function onAureliaStarted(aurelia: Aurelia): Promise<void> {
  aurelia.container.get(MetricsEventListener).register();
  aurelia.container.get(CustomValidationRules).register();
  const config: Configure = aurelia.container.get(Configure);
  return config.loadConfig().then(() => {
    const guiLanguage: GuiLanguage = aurelia.container.get(GuiLanguage);
    guiLanguage.apply();
  });
}

function preloadEntityTypes() {
  // This function does nothing, but its presence and dependence on these classes ensures that their decorators are evaluated.
  return [User, UserRole, Language, Metadata, ResourceKind, Resource, Workflow] && undefined;
}
