import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {MetadataRepository} from "../../../resources-config/metadata/metadata-repository";
import {Metadata} from "../../../resources-config/metadata/metadata";
import {ResourceClassTranslationValueConverter} from "../../value-converters/resource-class-translation-value-converter";

@autoinject
export class MetadataBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private metadataRepository: MetadataRepository,
              private i18n: I18N,
              private resourceClassTranslationValueConverter: ResourceClassTranslationValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    const metadata = await this.metadataRepository.get(navigationInstruction.params.id);
    const metadataParents = await this.metadataRepository.getHierarchy(metadata.id);
    const breadcrumbs: BreadcrumbItem[] = [this.metadataBreadcrumb(metadata)];
    for (let parent of metadataParents) {
      breadcrumbs.unshift(this.metadataBreadcrumb(parent));
    }
    breadcrumbs.unshift({
      label: this.resourceClassTranslationValueConverter.toView('metadata', metadata.resourceClass),
      route: 'metadata',
      params: {resourceClass: metadata.resourceClass}
    });
    return breadcrumbs;
  }

  private metadataBreadcrumb(metadata: Metadata): BreadcrumbItem {
    return {
      label: this.i18n.tr('Metadata') + ` #${metadata.id} (${metadata.name})`,
      route: 'metadata/details',
      params: {id: metadata.id},
      replace: true
    };
  }
}
