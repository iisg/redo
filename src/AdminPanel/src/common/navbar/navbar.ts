import {EventAggregator} from "aurelia-event-aggregator";
import {autoinject} from "aurelia-dependency-injection";
import {NavModel, Router} from "aurelia-router";
import {UpdateNavbarButtonsEvent} from "./update-navbar-buttons-event";

@autoinject()
export class Navbar {
  private readonly eventAggregator: EventAggregator;
  readonly router: Router;
  menuItems: NavModel[];

  constructor(eventAggregator: EventAggregator, router?: Router) {
    this.eventAggregator = eventAggregator;
    this.router = router;
    this.eventAggregator.subscribe("router:navigation:processing", this.clearSubmenu);
    this.eventAggregator.subscribe(UpdateNavbarButtonsEvent, this.updateSubmenu);
  }

  private clearSubmenu = (event) => {
    if (!event.instruction.config.hasChildRouter) {
      this.menuItems = [];
    }
  };

  private updateSubmenu = (event: UpdateNavbarButtonsEvent) => {
    this.menuItems = event.items;
  }
}
