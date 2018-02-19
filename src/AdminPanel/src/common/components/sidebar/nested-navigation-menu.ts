import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction, Router} from "aurelia-router";
import {RouteFilter} from "../../routes/route-filter";
import {AbstractRoute, NavRole} from "../../routes/routing-builder";
import {Configure} from "aurelia-configuration";
import {values} from "../../utils/object-utils";

@autoinject
export class NestedNavigationMenu {
  items: NavItem[];

  constructor(private eventAggregator: EventAggregator,
              private router: Router,
              private routeFilter: RouteFilter,
              private config: Configure) {
    eventAggregator.subscribe("router:navigation:complete", () => this.updateActiveItems());
    this.getMenuItems();
  }

  private updateActiveItems() {
    let currentInstruction = this.router.currentInstruction;
    if (!currentInstruction) {
      return;
    }
    this.items.forEach(item => item.updateActive(currentInstruction));
  }

  private getMenuItems() {
    const classes: string[] = this.getResourceClasses();

    const routeToLink: (className?: string, isResourceClass?: boolean) => (route: AbstractRoute) => NavLink =
      (className?: string, isResourceClass?: boolean) => (route: AbstractRoute) => {
        const labelKey = isResourceClass
          ? `resource_classes::${className}//${route.name}`
          : `nav::${route.title}`;
        if (route.route.indexOf(':resourceClass') == -1) {
          className = undefined;
        }
        return new NavLink(route.name, labelKey, route.settings.icon, className, route.settings.requiredRoles);
      };

    const top: NavLink[] = this.routeFilter.getRoutes(NavRole.TOP).map(routeToLink());
    const bottom: NavLink[] = this.routeFilter.getRoutes(NavRole.BOTTOM).map(routeToLink());

    let middle: NavItem[] = [];
    for (const className of classes) {
      const primary: NavLink[] = this.routeFilter.getRoutes(NavRole.PER_RESOURCE_CLASS, className).map(routeToLink(className, true));
      const secondary = new NavGroup('resource_classes::' + className + '//settings', className,
        this.routeFilter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY, className).map(routeToLink(className, true))
      );
      middle = middle.concat(primary).concat(secondary);
    }

    this.items = [].concat(top).concat(middle).concat(bottom);
  }

  private getResourceClasses(): string[] {
    const classMap: AnyMap<string> = this.config.get('resource_classes');
    return values(classMap);
  }
}

interface NavItem {
  labelKey: string;
  className?: string;
  active: boolean;

  updateActive(currentInstruction: NavigationInstruction): void;
}

class NavLink implements NavItem {
  params: {resourceClass: string};
  active: boolean = false;

  constructor(public routeName: string,
              public labelKey: string,
              public icon: string,
              public className?: string,
              public requiredRoles: string[] = []) {
    this.params = {resourceClass: className};
  }

  updateActive(currentInstruction: NavigationInstruction): void {
    if (this.routeName != currentInstruction.config.name) {
      this.active = false;
      return;
    }
    const paramNames = Object.keys(this.params).filter(key => this.params[key] !== undefined);
    for (const param of paramNames) {
      if (this.params[param] != currentInstruction.params[param]) {
        this.active = false;
        return;
      }
    }
    this.active = true;
  }
}

class NavGroup implements NavItem {
  expanded: boolean = false;
  active: boolean = false;

  constructor(public labelKey: string, public className: string, public items: NavLink[]) {
  }

  toggle(): void {
    this.expanded = !this.expanded;
  }

  updateActive(currentInstruction: NavigationInstruction): void {
    this.items.forEach(link => link.updateActive(currentInstruction));
    this.active = this.items.filter(link => link.active).length > 0;
    this.expanded = this.active;
  }
}
