import {AbstractRoute, NavRole} from "./routing-builder";
import {RouteProvider} from "./routes";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class RouteFilter {
  constructor(private routeProvider: RouteProvider) {
  }

  getRoutes(role: NavRole) {
    const routes = this.routeProvider.getRoutes();
    return this.filterByRole(routes, role);
  }

  private filterByRole(routes: AbstractRoute[], role: NavRole): AbstractRoute[] {
    return routes.filter(route => route.settings.role == role);
  }
}
