import {ResourceClassTranslationValueConverter} from './../../value-converters/resource-class-translation-value-converter';
import {ResourceKindRepository} from 'resources-config/resource-kind/resource-kind-repository';
import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {InCurrentLanguageValueConverter} from 'resources-config/multilingual-field/in-current-language';

@autoinject
export class ResourceKindBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private resourceKindRepository: ResourceKindRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private resourceClassTranslationValueConverter: ResourceClassTranslationValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    let resourceClass: string = navigationInstruction.params.resourceClass;
    const breadcrumbs: BreadcrumbItem[] = [];
    if (resourceClass) {
      breadcrumbs.push({label: this.resourceClassTranslationValueConverter.toView('New resource kind', resourceClass)});
    }
    else {
      const resourceKind = await this.resourceKindRepository.get(navigationInstruction.params.id);
      resourceClass = resourceKind.resourceClass;
      breadcrumbs.push({label: this.inCurrentLanguage.toView(resourceKind.label)});
    }
    breadcrumbs.unshift({
      label: this.resourceClassTranslationValueConverter.toView('resource-kinds', resourceClass),
      route: 'resource-kinds',
      params: {resourceClass}
    });
    return breadcrumbs;
  }
}
