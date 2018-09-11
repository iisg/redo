import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction, Router} from "aurelia-router";
import {values} from "lodash";
import {ContextResourceClass, ResourceClassChangeEvent} from "../../../resources/context/context-resource-class";
import {RouteFilter} from "../../routes/route-filter";
import {AbstractRoute, NavRole} from "../../routes/routing-builder";

@autoinject
export class NestedNavigationMenu {
  items: NavItem[];
  private currentClass: string;

  constructor(private eventAggregator: EventAggregator,
              private router: Router,
              private routeFilter: RouteFilter,
              private config: Configure) {
    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => this.updateResourceClass(event));
    eventAggregator.subscribe('router:navigation:complete', () => this.updateActiveItems());
    this.getMenuItems();
  }

  private updateResourceClass(event: ResourceClassChangeEvent): void {
    this.currentClass = event.newResourceClass;
  }

  private updateActiveItems() {
    let currentInstruction = this.router.currentInstruction;
    if (!currentInstruction) {
      return;
    }
    this.items.forEach(item => item.updateActive(currentInstruction, this.currentClass));
  }

  private getMenuItems() {
    const resourceClasses: string[] = this.getResourceClasses();

    const routeToLink: (className?: string, classIcon?: string) => (route: AbstractRoute) => NavLink =
      (className?: string, classIcon?: string) => (route: AbstractRoute) => {
        const labelKey = className
          ? `resource_classes::${className}//${route.name}`
          : `navigation::${route.title}`;
        if (route.route.indexOf(':resourceClass') == -1) {
          className = undefined;
        }
        classIcon = classIcon ? classIcon : route.settings.icon;
        const navLink = new NavLink(route.name, labelKey, classIcon, className, route.settings.requiredRole);
        classIcon = undefined;
        return navLink;
      };

    const top: NavLink[] = this.routeFilter.getRoutes(NavRole.TOP).map(routeToLink());
    const bottom: NavLink[] = this.routeFilter.getRoutes(NavRole.BOTTOM).map(routeToLink());

    let middle: NavItem[] = [];
    for (const resourceClass of resourceClasses) {
      const classIcon = this.getIconForResourceClass(resourceClass);
      const primary: NavLink[] = this.routeFilter.getRoutes(NavRole.PER_RESOURCE_CLASS).map(routeToLink(resourceClass, classIcon));
      const secondary = new NavGroup('resource_classes::' + resourceClass + '//settings', resourceClass,
        this.routeFilter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY).map(routeToLink(resourceClass))
      );
      middle = middle.concat(primary).concat(secondary);
    }
    this.items = [].concat(top).concat(middle).concat(bottom);
  }

  private getResourceClasses(): string[] {
    const classMap: AnyMap<string> = this.config.get('resource_classes');
    return values(classMap);
  }

  private getIconForResourceClass(resourceClass: string): string {
    return this.config.get('resource_classes_icons')[resourceClass];
  }
}

interface NavItem {
  labelKey: string;
  className?: string;
  active: boolean;

  updateActive(currentInstruction: NavigationInstruction, currentClass: string): void;
}

class NavLink implements NavItem {
  params: { resourceClass: string };
  active: boolean = false;

  constructor(public routeName: string,
              public labelKey: string,
              public icon: string,
              public resourceClass?: string,
              public requiredRole?: string) {
    this.params = {resourceClass};
  }

  updateActive(currentInstruction: NavigationInstruction, currentClass: string): void {
    const linkConfig = currentInstruction.config.name.match('^[^\\/]+');
    const resourceClass = currentInstruction.params.resourceClass;
    const currentLink = `${this.routeName}/${this.resourceClass}`;
    let parentLink = `${linkConfig}/${resourceClass}`;
    if (currentClass) {
      parentLink = `${linkConfig}/${currentClass}`;
    }
    if (currentLink == parentLink) {
      this.active = true;
      return;
    }
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
  requiredRole: string;
  resourceClass: string;

  constructor(public labelKey: string, public className: string, public items: NavLink[]) {
    this.requiredRole = items[0].requiredRole;
    this.resourceClass = items[0].resourceClass;
  }

  toggle(): void {
    this.expanded = !this.expanded;
  }

  updateActive(currentInstruction: NavigationInstruction, linkClass: string): void {
    this.items.forEach(link => link.updateActive(currentInstruction, linkClass));
    this.active = this.items.filter(link => link.active).length > 0;
    this.expanded = this.active;
  }
}
