import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {NavbarTitleProvider} from "./navbar-title";
import {ResourceRepository} from "../../../resources/resource-repository";

@autoinject
export class ResourceClassDetailsNavbarTitleProvider implements NavbarTitleProvider {

  constructor(private resourceRepository: ResourceRepository) {
  }

  async getNavbarTitle(navigationInstruction: NavigationInstruction): Promise<string> {
    const resource = await this.resourceRepository.get(navigationInstruction.params.id);
    const configName = navigationInstruction.config.name;
    if (resource.resourceClass !== undefined) {
      return `resource_classes::${resource.resourceClass}//${configName}`;
    }
    else {
      return `resource_classes::${configName}`;
    }
  }

  supports(navigationInstruction: NavigationInstruction) {
    return navigationInstruction.params.hasOwnProperty("id");
  }

}