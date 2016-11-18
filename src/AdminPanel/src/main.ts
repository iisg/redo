import "bootstrap";
import "arrive";
import "bootstrap-material-design";
import "bootstrap-select";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";
import {configure as configureHttpClient} from "./config/http-client";
import {CurrentUser} from "./common/user/current-user";
import {CurrentUserFetcher} from "./common/user/current-user-fetcher";
import {MetricsCollector} from "./common/metrics/metrics-collector";
import {MetricsEventListener} from "./common/metrics/metrics-event-listener";
import {CustomValidationRules} from "./common/validation/custom-validation-rules";

MetricsCollector.timeStart("bootstrap");

LogManager.addAppender(new ConsoleAppender());
LogManager.setLevel(LogManager.logLevel.info);

export function configure(aurelia: Aurelia) {
  aurelia.use
    .standardConfiguration()
    .plugin('aurelia-animator-css')
    .plugin('aurelia-configuration', config => config.setDirectory('/api/'))
    .plugin('aurelia-validation');

  configureHttpClient(aurelia);

  aurelia.container.get(CurrentUserFetcher).fetch()
    .then(user => aurelia.container.registerInstance(CurrentUser, user))
    .then(() => aurelia.start())
    .then(() => aurelia.container.get(MetricsEventListener).register())
    .then(() => aurelia.container.get(CustomValidationRules).register())
    .then(() => aurelia.setRoot())
    .then(() => MetricsCollector.timeEnd("bootstrap"));
}
