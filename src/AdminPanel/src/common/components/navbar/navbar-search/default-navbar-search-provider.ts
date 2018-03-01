import {NavbarSearchProvider} from "./navbar-search";
import {NavigationInstruction} from "aurelia-router";

export class DefaultNavbarSearchProvider implements NavbarSearchProvider {
  async getResourceClass(navigationInstruction: NavigationInstruction): Promise<string> {
    return "";
  }
}
