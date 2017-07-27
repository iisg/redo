import {Router, RouterConfiguration, ConfiguresRouter} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {RouteAccessChecker} from "./common/routes/route-access-checker";
import {SidebarConfig} from "./sidebar-config/sidebar-config";

@autoinject
export class App implements ConfiguresRouter, ComponentAttached {
  router: Router;

  constructor(private i18n: I18N,
              private element: Element,
              private routeAccessChecker: RouteAccessChecker,
              private sidebar: SidebarConfig) {
  }

  configureRouter(config: RouterConfiguration, router: Router) {
    config.title = 'RePeKa';
    config.options.pushState = true;
    config.options.root = '/admin';
    config.map(this.sidebar.getRoutes());
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
