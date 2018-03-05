import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {NavbarTitleProvider} from "./navbar-title";

@autoinject
export class ResourceClassNavbarTitleProvider implements NavbarTitleProvider {

  async getNavbarTitle(navigationInstruction: NavigationInstruction): Promise<string> {
    const resourceClass = navigationInstruction.params.resourceClass;
    const configName = navigationInstruction.config.name;
    return `resource_classes::${resourceClass}//${configName}`;
  }

  supports(navigationInstruction: NavigationInstruction) {
    return navigationInstruction.params.hasOwnProperty("resourceClass");
  }

}