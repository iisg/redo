import {NavbarSearchProvider} from "./navbar-search";
import {NavigationInstruction} from "aurelia-router";
import {MetadataRepository} from "../../../../resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class MetadataNavbarSearchProvider implements NavbarSearchProvider {
  constructor(private metadataRepository: MetadataRepository) {
  }

  async getResourceClass(navigationInstruction: NavigationInstruction): Promise<string> {
    if (navigationInstruction.params.hasOwnProperty('resourceClass')) {
      return navigationInstruction.params.resourceClass;
    }
    const metadata = await this.metadataRepository.get(navigationInstruction.params.id);
    return metadata.resourceClass;
  }
}
