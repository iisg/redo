import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {ConfiguresRouter, RouterConfiguration} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";
import {routes} from "common/routes/routes";
import {I18nParams} from "config/i18n";
import {ChangeLossPreventer} from "./common/change-loss-preventer/change-loss-preventer";
import {RouteAccessChecker} from "./common/routes/route-access-checker";

@autoinject
export class App implements ConfiguresRouter, ComponentAttached {
  constructor(private i18n: I18N,
              private i18nParams: I18nParams,
              private element: Element,
              private routeAccessChecker: RouteAccessChecker, private changeLossPreventer: ChangeLossPreventer) {
  }

  configureRouter(configuration: RouterConfiguration) {
    configuration.title = this.i18nParams.applicationName;
    configuration.options.pushState = true;
    configuration.map(routes);
    configuration.mapRoute({route: ['admin/not-allowed'], name: 'not-allowed', moduleId: 'common/error-pages/not-allowed'});
    const pageNotFoundRouteConfiguration = {route: ['admin/not-found'], name: 'not-found', moduleId: 'common/error-pages/not-found'};
    configuration.mapRoute(pageNotFoundRouteConfiguration);
    configuration.mapUnknownRoutes(pageNotFoundRouteConfiguration);
    configuration.addAuthorizeStep(this.changeLossPreventer);
    configuration.addAuthorizeStep(this.routeAccessChecker);
  }

  attached() {
    this.i18n.updateTranslations(this.element);
    this.disableChangingNumberFieldsValueByScrolling();
  }

  private disableChangingNumberFieldsValueByScrolling() {
    $(document).on("wheel", "input[type=number]:focus", function(event) {
      (event.target as HTMLInputElement).type = '';
      setTimeout(() => (event.target as HTMLInputElement).type = 'number');
    });
  }
}
