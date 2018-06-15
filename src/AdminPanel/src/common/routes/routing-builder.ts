import {RouteConfig} from "aurelia-router";
import * as $ from "jquery";

interface Arrayable<T> {
  toArray(): T[];
}

export class RoutingBuilder implements Arrayable<AbstractRoute> {
  public readonly routes: AbstractRoute[];

  constructor(routes: Arrayable<AbstractRoute>[]) {
    this.routes = [].concat.apply([], routes.map(r => r.toArray()));
  }

  toArray(): AbstractRoute[] {
    return this.routes;
  }
}

type ActivationStrategy = 'no-change' | 'invoke-lifecycle' | 'replace';

export enum NavRole { TOP, PER_RESOURCE_CLASS, PER_RESOURCE_CLASS_SECONDARY, BOTTOM }

export interface RouteSettings {
  icon: string;
  role: NavRole;
  requiredRole?: string;
  breadcrumbsProvider?: string;
}

export abstract class AbstractRoute implements RouteConfig, Arrayable<AbstractRoute> {
  route: string;
  name: string;
  moduleId: string;
  title: string;
  settings: RouteSettings = {icon: undefined, role: undefined};
  activationStrategy: ActivationStrategy = 'invoke-lifecycle';

  requireRole(role: string): this {
    this.settings.requiredRole = role;
    return this;
  }

  toArray(): AbstractRoute[] {
    return [this];
  }
}

export class Route extends AbstractRoute {
  constructor(url: string, name: string, moduleId: string) {
    super();
    this.route = url;
    this.name = name;
    this.moduleId = moduleId;
  }

  withMenuItem(title: string, role: NavRole, icon?: string): Route {
    this.title = title;
    $.extend(this.settings, {icon, role});
    return this;
  }

  setActivationStrategy(activationStrategy: ActivationStrategy): Route {
    this.activationStrategy = activationStrategy;
    return this;
  }

  withBreadcrumbsProvider(providerName: string): Route {
    this.settings.breadcrumbsProvider = providerName;
    return this;
  }
}

/**
 * Just like Route but has no name and no menu item title (will inherit them from RouteGroup).
 * BaseRoute is NOT required in each RouteGroup, it's just a convenience class.
 */
export class BaseRoute extends AbstractRoute {
  constructor(url: string, moduleId: string) {
    super();
    this.route = url;
    this.name = '';
    this.moduleId = moduleId;
  }

  withMenuItem(role: NavRole, icon?: string): BaseRoute {
    $.extend(this.settings, {icon, role});
    return this;
  }
}

export class RouteGroup extends RoutingBuilder {
  constructor(urlBase: string, nameBase: string, moduleBase: string, defaultTitle: string, routes: Arrayable<AbstractRoute>[]) {
    super(routes);
    for (const route of this.routes) {
      route.route = this.join(urlBase, route.route || '');
      route.name = this.join(nameBase, route.name || '');
      route.moduleId = this.join(moduleBase, route.moduleId);
      route.title = route.title || defaultTitle;
    }
  }

  private join(s1: string, s2: string): string {
    const slashless1 = this.removeTrailingSlash(s1);
    if (s2 == '') {
      return slashless1;
    } else {
      return slashless1 + '/' + s2;
    }
  }

  private removeTrailingSlash(str: string): string {
    if (str.length == 0 || str[str.length - 1] != '/') {
      return str;
    } else {
      return str.substr(0, str.length - 1);
    }
  }

  toArray(): AbstractRoute[] {
    return [].concat.apply([], this.routes.map(r => r.toArray()));
  }
}
