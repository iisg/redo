import {EventAggregator} from "aurelia-event-aggregator";
import {autoinject} from "aurelia-dependency-injection";
import {NavItemWithChildren, ActivatableNavItem, RouteDefinition} from "./route-types";

/**
 * Groups the routes definitions in parent-children relation for every nested route.
 * @see http://stackoverflow.com/a/37378720/878514
 */
@autoinject
export class NestedNavigationValueConverter implements ToViewValueConverter {

  constructor(private eventAggregator: EventAggregator) {
  }

  toView(navItems: RouteDefinition[]): ActivatableNavItem[] {
    let menuItems = [];
    let parents = {};
    for (let navItem of navItems) {
      const parentTitle = navItem.settings.parentTitle;
      if (parentTitle) {
        if (!parents[parentTitle]) {
          parents[parentTitle] = new ParentNavItem(navItem, this.eventAggregator, parents);
          menuItems.push(parents[parentTitle]);
        }
        parents[parentTitle].children.push(navItem);
      } else {
        menuItems.push(navItem);
      }
    }
    return menuItems;
  }
}

class ParentNavItem extends ActivatableNavItem implements NavItemWithChildren {
  children: ActivatableNavItem[] = [];
  expanded = false;

  constructor(model: RouteDefinition, eventAggregator: EventAggregator, private allParents: Object) {
    super();
    this.title = model.settings.parentTitle;
    this.settings = {icon: model.settings.parentIcon};
    eventAggregator.subscribe("router:navigation:complete", () => this.expanded = this.isAnyChildActive());
  }

  setExpanded(expanded: boolean) {
    this.expanded = expanded;
    if (expanded) {
      for (let title in this.allParents) {
        let someParent = this.allParents[title];
        if (title != this.title && someParent.expanded && !this.isAnyChildActive(someParent)) {
          someParent.expanded = false;
        }
      }
    }
  }

  toggle() {
    this.setExpanded(!this.expanded);
  }

  private isAnyChildActive(item: ParentNavItem = this) {
    for (let child of item.children) {
      if (child.isActive) {
        return true;
      }
    }
    return false;
  }
}
