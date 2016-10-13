import {EventAggregator} from "aurelia-event-aggregator";
import {Navbar} from "./navbar";
import {NavModel} from "aurelia-router";
import {UpdateNavbarButtonsEvent} from "./update-navbar-buttons-event";

describe('Navbar', () => {
  let eventAggregator: EventAggregator;
  let navbar: Navbar;
  let processEvent;
  let navModels: NavModel[];

  function newNavModel(title: string) {
    let navModel = new NavModel(undefined, "/" + title);
    navModel.setTitle(title);
    return navModel;
  }

  beforeEach(() => {
    eventAggregator = new EventAggregator();
    navbar = new Navbar(eventAggregator);
    navModels = [newNavModel("A"), newNavModel("B")];
    processEvent = {
      instruction: {
        config: {}
      }
    };
  });

  it('adds submenu items when router has one', () => {
    eventAggregator.publish(new UpdateNavbarButtonsEvent({navigation: navModels}));
    expect(navbar.menuItems).toBe(navModels);
  });

  it('clears submenu on new route processing', () => {
    eventAggregator.publish(new UpdateNavbarButtonsEvent({navigation: navModels}));
    eventAggregator.publish("router:navigation:processing", processEvent);
    expect(navbar.menuItems).toBeDefined();
    expect(navbar.menuItems.length).toBe(0);
  });

  it('does not clear the menu items if the router is a child router', () => {
    eventAggregator.publish(new UpdateNavbarButtonsEvent({navigation: navModels}));
    processEvent.instruction.config.hasChildRouter = true;
    eventAggregator.publish("router:navigation:processing", processEvent);
    expect(navbar.menuItems.length).toBe(2);
  });

  it('displays the newest subitems', () => {
    eventAggregator.publish(new UpdateNavbarButtonsEvent({navigation: navModels}));
    eventAggregator.publish(new UpdateNavbarButtonsEvent({navigation: []}));
    expect(navbar.menuItems.length).toBe(0);
  });

});
