import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {ConfiguresRouter, RouterConfiguration} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import {routes} from "common/routes/routes";
import {supportMiddleClickInLinks} from "./common/routes/middle-link-opener";
import {RouteAccessChecker} from "./common/routes/route-access-checker";

@autoinject
export class App implements ConfiguresRouter, ComponentAttached {
  constructor(private i18n: I18N,
              private element: Element,
              private routeAccessChecker: RouteAccessChecker) {
  }

  configureRouter(configuration: RouterConfiguration) {
    configuration.title = 'RePeKa';
    configuration.options.pushState = true;
    configuration.options.root = '/admin';
    configuration.map(routes);
    configuration.mapRoute({route: ['not-allowed'], name: 'not-allowed', moduleId: 'common/error-pages/not-allowed'});
    const pageNotFoundRouteConfiguration = {route: ['not-found'], name: 'not-found', moduleId: 'common/error-pages/not-found'};
    configuration.mapRoute(pageNotFoundRouteConfiguration);
    configuration.mapUnknownRoutes(pageNotFoundRouteConfiguration);
    configuration.addAuthorizeStep(this.routeAccessChecker);
    supportMiddleClickInLinks(configuration);
  }

  attached() {
    this.i18n.updateTranslations(this.element);
  }
}
