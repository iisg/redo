import {Router, RouterConfiguration, ConfiguresRouter} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import routes from "./routes";

export class App implements ConfiguresRouter, ComponentAttached {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map(routes);
    this.router = router;
  }

  attached() {
    $.material.init();
  }
}
