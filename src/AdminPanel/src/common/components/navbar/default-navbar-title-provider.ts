import {NavigationInstruction} from "aurelia-router";
import {NavbarTitleProvider} from "./navbar-title";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class DefaultNavbarTitleProvider implements NavbarTitleProvider {

  async getNavbarTitle(navigationInstruction: NavigationInstruction): Promise<string> {
    const configTitle = navigationInstruction.config.title;
    return`nav::${configTitle}`;
  }

  supports(navigationInstruction: NavigationInstruction) {
    return true;
  }
}