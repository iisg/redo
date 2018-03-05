import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {inlineView} from "aurelia-templating";
import {ResourceClassDetailsNavbarTitleProvider} from './resourceClassDetails-navbar-title-provider';
import {ResourceClassNavbarTitleProvider} from "./resourceClass-navbar-title-provider";
import {DefaultNavbarTitleProvider} from "./default-navbar-title-provider";

@inlineView('<template><span class="navbar-brand">${title | t}</span></template>')
export class NavbarTitle {
  title: string;
  private providers: Array<NavbarTitleProvider> = [];

  constructor(eventAggregator: EventAggregator,
              private resourceClassDetailsNavbarTitleProvider: ResourceClassDetailsNavbarTitleProvider,
              private resourceClassNavbarTitleProvider: ResourceClassNavbarTitleProvider,
              private defaultNavbarTitleProvider: DefaultNavbarTitleProvider) {

    eventAggregator.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.updateTitle(event.instruction));
    this.providers.push(resourceClassNavbarTitleProvider);
    this.providers.push(resourceClassDetailsNavbarTitleProvider);
    this.providers.push(defaultNavbarTitleProvider);
  }

  async updateTitle(navigationInstruction: NavigationInstruction) {
    for (let provider of this.providers) {
      if (provider.supports(navigationInstruction)) {
        this.title = await provider.getNavbarTitle(navigationInstruction);
        break;
      }
    }
  }
}

export interface NavbarTitleProvider {
  getNavbarTitle(navigationInstruction: NavigationInstruction): Promise<string>;
  supports(navigationInstruction: NavigationInstruction): boolean;
}