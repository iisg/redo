import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {inlineView} from "aurelia-templating";

@inlineView('<template><span class="navbar-brand">${title | t}</span></template>')
export class NavbarTitle {
  title: string;

  constructor(eventAggregator: EventAggregator) {
    eventAggregator.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.updateTitle(event.instruction));
  }

  updateTitle(navigationInstruction: NavigationInstruction) {
    this.title = navigationInstruction.params.hasOwnProperty("resourceClass")
      ? `resource_classes::${navigationInstruction.params.resourceClass}//${navigationInstruction.config.name}`
      : `nav::${navigationInstruction.config.title}`;
  }
}