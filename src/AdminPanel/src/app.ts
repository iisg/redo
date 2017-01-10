import {Router, RouterConfiguration, ConfiguresRouter} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {EventAggregator} from "aurelia-event-aggregator";
import routes from "./routes";
import {RouteAccessChecker} from "./common/routes/route-access-checker";

@autoinject
export class App implements ConfiguresRouter, ComponentAttached {
  router: Router;

  constructor(private i18n: I18N, private element: Element, ea: EventAggregator, private routeAccessChecker: RouteAccessChecker) {
    ea.subscribe('i18n:locale:changed', () => {
      this.i18n.updateTranslations(this.element);
      if (this.router) {
        this.router.updateTitle();
      }
      ea.publish('i18n:translation:finished');
    });
  }

  configureRouter(config: RouterConfiguration, router: Router) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map(routes);
    config.map([{route: ['not-allowed'], name: 'not-allowed', moduleId: 'common/error-pages/not-allowed'}]);
    config.fallbackRoute('');
    config.mapUnknownRoutes('common/error-pages/not-found');
    config.addAuthorizeStep(this.routeAccessChecker);
    this.router = router;
  }

  attached() {
    $.material.init();
    this.i18n.updateTranslations(this.element);
  }
}
