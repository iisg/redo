import "bootstrap";
import "arrive";
import "bootstrap-material-design";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";
import {configure as configureHttpClient} from "./config/http-client";
import {CurrentUser} from "./common/user/current-user";
import {CurrentUserFetcher} from "./common/user/current-user-fetcher";

LogManager.addAppender(new ConsoleAppender());
LogManager.setLevel(LogManager.logLevel.info);

export function configure(aurelia: Aurelia) {
  aurelia.use
    .standardConfiguration()
    .plugin('aurelia-html-import-template-loader')
    .plugin('aurelia-configuration');

  configureHttpClient(aurelia);

  aurelia.container.get(CurrentUserFetcher).fetch()
    .then(user => aurelia.container.registerInstance(CurrentUser, user))
    .then(() => aurelia.start())
    .then(() => aurelia.setRoot());
}
