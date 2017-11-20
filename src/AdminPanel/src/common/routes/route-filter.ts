import {NavRole, AbstractRoute} from "./routing-builder";
import {RouteProvider} from "./routes";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class RouteFilter {
  constructor(private routeProvider: RouteProvider) {
  }

  getRoutes(role: NavRole, className?: string) {
    this.checkRoleClassPair(role, className);
    const perClassRole: boolean = role == (NavRole.PER_RESOURCE_CLASS) || (role == NavRole.PER_RESOURCE_CLASS_SECONDARY);
    const routes = this.routeProvider.getRoutes();
    if (!perClassRole) {
      return this.filterByRole(routes, role);
    } else if (role == NavRole.PER_RESOURCE_CLASS_SECONDARY) {
      // secondary per-class items: concatenate class-agnostic and class-specific
      const secondaryItems = this.filterByRole(routes, NavRole.PER_RESOURCE_CLASS_SECONDARY);
      return this.filterClassAgnostic(secondaryItems).concat(this.filterClassSpecific(secondaryItems, className));
    } else if (role == NavRole.PER_RESOURCE_CLASS) {
      // primary per-class items: class-specific items override all class-agnostic items
      const primaryItems = this.filterByRole(routes, NavRole.PER_RESOURCE_CLASS);
      const classAgnostic = this.filterClassAgnostic(primaryItems);
      const classSpecific = this.filterClassSpecific(primaryItems, className);
      return classSpecific.length > 0 ? classSpecific : classAgnostic;
    } else {
      throw new Error("This shouldn't happen - check RouteFilter's logic");
    }
  }

  private checkRoleClassPair(role: NavRole, className: string): void {
    const perClassRole: boolean = (role == NavRole.PER_RESOURCE_CLASS) || (role == NavRole.PER_RESOURCE_CLASS_SECONDARY);
    if (perClassRole && className === undefined) {
      throw new Error('Class name is required for PER_RESOURCE_CLASS and PER_RESOURCE_CLASS_SECONDARY items');
    } else if (!perClassRole && className !== undefined) {
      throw new Error("Class name is not allowed for roles other than PER_RESOURCE_CLASS and PER_RESOURCE_CLASS_SECONDARY."
        + "Check route definitions.");
    }
  }

  private filterByRole(routes: AbstractRoute[], role: NavRole): AbstractRoute[] {
    return routes.filter(route => route.settings.role == role);
  }

  private filterClassAgnostic(routes: AbstractRoute[]): AbstractRoute[] {
    return routes.filter(route => route.settings.specificForClass == undefined);
  }

  private filterClassSpecific(routes: AbstractRoute[], className: string): AbstractRoute[] {
    return routes.filter(route => route.settings.specificForClass == className);
  }
}
