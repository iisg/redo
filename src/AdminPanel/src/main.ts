import "arrive";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";
import "bootstrap";
import {MetricsCollector} from "common/metrics/metrics-collector";
import {MetricsEventListener} from "common/metrics/metrics-event-listener";
import {CustomValidationRules} from "common/validation/custom-validation-rules";
import {installValidationMessageLocalization} from "common/validation/validation-message-localization";
import {configure as configureHttpClient} from "config/http-client";
import {i18nConfiguratorFactory} from "config/i18n";
import {CurrentUserFetcher} from "users/current/current-user-fetcher";
import {GuiLanguage} from "./common/i18n/gui-language";
import {dialogConfigurator} from "./config/dialog";
import {Language} from "./resources-config/language-config/language";
import {Metadata} from "./resources-config/metadata/metadata";
import {ResourceKind} from "./resources-config/resource-kind/resource-kind";
import {WorkflowIdMapper} from "./resources-config/resource-kind/resource-kind-mapping";
import {Resource} from "./resources/resource";
import {ResourceMapper} from "./resources/resource-mapping";
import {User} from "./users/user";
import {Workflow} from "./workflows/workflow";
import "polyfills";

MetricsCollector.timeStart("bootstrap");

LogManager.addAppender(new ConsoleAppender());
LogManager.setLevel(LogManager.logLevel.info);

const adminPanel = window.location.pathname.startsWith('/admin');

export function configure(aurelia: Aurelia) {
  if (adminPanel) {
    aurelia.use
      .standardConfiguration()
      .plugin('aurelia-animator-css')
      .plugin('aurelia-validation')
      .plugin('aurelia-cookie')
      .plugin('aurelia-i18n', i18nConfiguratorFactory(aurelia))
      .plugin('aurelia-dialog', dialogConfigurator)
      .plugin("oribella-aurelia-sortable")
      .plugin('aurelia-plugins-tabs')
      .globalResources([
        'common/authorization/has-role-value-converter',
        'common/bootstrap/bootstrap-tooltip',
        'common/components/icon/icon',
        'common/components/go-to-link-on-row-click/go-to-link-on-row-click',
        'common/components/loading-bar/loading-bar.html',
        'common/components/loading-bar/throbber.html',
        'common/components/disabled-link/disabled-link',
        'resources-config/multilingual-field/multilingual-editor',
        'resources-config/multilingual-field/in-current-language',
        'common/http-client/invalid-command-message.html', // Used in alerts by GlobalExceptionInterceptor.
        'resources/details/resource-link',
        'resources/details/resource-label-value-converter',
        'common/value-converters/resource-class-translation-value-converter'
      ]);
  } else {
    aurelia.use
      .standardConfiguration()
      .plugin('aurelia-animator-css')
      .plugin('aurelia-validation')
      .plugin('aurelia-i18n', i18nConfiguratorFactory(aurelia))
      .plugin('aurelia-dialog', dialogConfigurator)
      .globalResources([
      'common/custom-attributes/local-storage-value-custom-attribute',
      'common/components/redo-footer/redo-footer.html',
      'common/components/redo-logo/redo-logo.html',
      'common/components/icon/icon',
      'themes/admin-styles-loader',
      'themes/redo/search-bar',
    ]);
  }

  preloadEntityTypes();
  configureHttpClient(aurelia);
  installValidationMessageLocalization(aurelia);

  (adminPanel ? aurelia.container.get(CurrentUserFetcher).fetch() : Promise.resolve(undefined))
    .then(user => aurelia.container.registerInstance(CurrentUserFetcher.CURRENT_USER_KEY, user))
    .then(() => aurelia.start())
    .then(() => onAureliaStarted(aurelia))
    .then(() => {
      adminPanel ? aurelia.setRoot() : aurelia.enhance();
    })
    .then(() => MetricsCollector.timeEnd("bootstrap"));
}

function onAureliaStarted(aurelia: Aurelia): void {
  if (adminPanel) {
    aurelia.container.get(MetricsEventListener).register();
    aurelia.container.get(CustomValidationRules).register();
    aurelia.container.get(GuiLanguage).apply();
  }
}

function preloadEntityTypes() {
  // This function does nothing, but its presence and dependence on these classes ensures that their decorators are evaluated.
  return [User, Language, Metadata, ResourceKind, ResourceMapper, Resource, Workflow, WorkflowIdMapper] && undefined;
}
