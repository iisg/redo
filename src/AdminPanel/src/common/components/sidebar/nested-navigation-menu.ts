import {autoinject} from "aurelia-dependency-injection";
import {SiteMenu} from "./site-menu";
import {EventAggregator} from "aurelia-event-aggregator";
import {Router} from "aurelia-router";
import {RouteConfig} from "aurelia-router";
import {shallowEquals} from "../../utils/object-utils";
import {NavItem} from "../../routes/route-types";

@autoinject
export class NestedNavigationMenu {

  constructor(private siteMenu: SiteMenu, private eventAggregator: EventAggregator, private router: Router) {
    eventAggregator.subscribe("router:navigation:complete", () => this.updateSiteMenu());
  }

  updateSiteMenu() {
    for (let item of this.siteMenu.navigation) {
      if (item.children) {
        item.isActive = item.expanded = this.isAnyChildActive(item);
      } else {
        item.isActive = this.isItemActive(item);
      }
    }
  }

  private isItemActive(item: RouteConfig): boolean {
    const currentInstruction = this.router.currentInstruction;
    if (currentInstruction) {
      return item.route == currentInstruction.config.name && shallowEquals(item.settings.params, currentInstruction.params);
    } else {
      return false;
    }
  }

  toggle(item: NavItem) {
    item.expanded = !item.expanded;
    if (item.expanded) {
      this.collapseOthers(item);
    }
  }

  private collapseOthers(item: NavItem) {
    for (let navigationItem of this.siteMenu.navigation) {
      if (navigationItem.children) {
        if (navigationItem.title != item.title && navigationItem.expanded && !this.isAnyChildActive(navigationItem)) {
          navigationItem.expanded = false;
        }
      }
    }
  }

  private isAnyChildActive(item: NavItem) {
    let anyChildActive = false;
    for (let child of item.children) {
      child.isActive = this.isItemActive(child);
      if (child.isActive) {
        anyChildActive = true;
      }
    }
    return anyChildActive;
  }
}
