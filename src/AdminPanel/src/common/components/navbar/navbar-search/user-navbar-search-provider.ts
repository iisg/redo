import {NavbarSearchProvider} from "./navbar-search";
import {NavigationInstruction} from "aurelia-router";

export class UserNavbarSearchProvider implements NavbarSearchProvider {
  async getResourceClass(navigationInstruction: NavigationInstruction): Promise<string> {
    return "users";
  }
}
