import {ConfiguresRouter, RouterConfiguration} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {RouteAccessChecker} from "./common/routes/route-access-checker";
import {routes} from "common/routes/routes";
import {supportMiddleClickInLinks} from "./common/routes/middle-link-opener";

@autoinject
export class App implements ConfiguresRouter, ComponentAttached {
  constructor(private i18n: I18N,
              private element: Element,
              private routeAccessChecker: RouteAccessChecker) {
  }

  configureRouter(config: RouterConfiguration) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map(routes);
    config.mapRoute({route: ['not-allowed'], name: 'not-allowed', moduleId: 'common/error-pages/not-allowed'});
    config.fallbackRoute('');
    config.mapUnknownRoutes('common/error-pages/not-found');
    config.addAuthorizeStep(this.routeAccessChecker);
    supportMiddleClickInLinks(config);
  }

  attached() {
    this.i18n.updateTranslations(this.element);
  }
}
