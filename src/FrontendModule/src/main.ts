import "materialize-css";
import {Aurelia, LogManager} from "aurelia-framework";
import {ConsoleAppender} from "aurelia-logging-console";

LogManager.addAppender(new ConsoleAppender());
LogManager.setLevel(LogManager.logLevel.info);

export function configure(aurelia: Aurelia) {
  aurelia.use
    .standardConfiguration()
    .plugin('aurelia-html-import-template-loader')
    .plugin('aurelia-configuration')
    .plugin('aurelia-materialize-bridge', bridge => bridge.useAll());

  aurelia.start().then(() => aurelia.setRoot());
}
