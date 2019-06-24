import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../../../resources/resource-repository";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {Resource} from "../../../resources/resource";
import {ResourceLabelValueConverter} from "../../../resources/details/resource-label-value-converter";
import {ResourceClassTranslationValueConverter} from "common/value-converters/resource-class-translation-value-converter";

@autoinject
export class ResourceBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private resourceRepository: ResourceRepository,
              private resourceLabel: ResourceLabelValueConverter,
              private resourceClassTranslation: ResourceClassTranslationValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    const resource = await this.resourceRepository.getTeaser(navigationInstruction.params.id);
    const resources = await this.resourceRepository.getHierarchy(resource.id);
    const breadcrumbs: BreadcrumbItem[] = [this.resourceBreadcrumb(resource)];
    for (let resource of resources) {
      breadcrumbs.unshift(this.resourceBreadcrumb(resource));
    }
    breadcrumbs.unshift({
      label: this.resourceClassTranslation.toView('resources', resource.resourceClass),
      route: 'resources',
      params: {resourceClass: resource.resourceClass},
    });
    return breadcrumbs;
  }

  protected resourceBreadcrumb(resource: Resource): BreadcrumbItem {
    return {
      label: this.resourceLabel.toView(resource),
      labelHtml: true,
      route: resource.canView && 'resources/details',
      params: {id: resource.id},
      replace: true,
    };
  }
}
