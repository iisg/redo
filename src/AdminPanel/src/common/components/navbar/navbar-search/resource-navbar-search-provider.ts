import {NavbarSearchProvider} from "./navbar-search";
import {NavigationInstruction} from "aurelia-router";
import {ResourceRepository} from "../../../../resources/resource-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceNavbarSearchProvider implements NavbarSearchProvider {
  constructor(private resourceRepository: ResourceRepository) {
  }

  async getResourceClass(navigationInstruction: NavigationInstruction): Promise<string> {
    if (navigationInstruction.params.hasOwnProperty('resourceClass')) {
      return navigationInstruction.params.resourceClass;
    }
    const resource = await this.resourceRepository.get(navigationInstruction.params.id);
    return resource.resourceClass;
  }
}
