import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../../../resources/resource-repository";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {ResourceDisplayStrategyValueConverter} from "../../../resources-config/resource-kind/display-strategies/resource-display-strategy";
import {Resource} from "../../../resources/resource";

@autoinject
export class ResourceBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private resourceRepository: ResourceRepository, protected i18n: I18N,
              private resourceDisplayStrategy: ResourceDisplayStrategyValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    const resource = await this.resourceRepository.get(navigationInstruction.params.id);
    const resources = await this.resourceRepository.getHierarchy(resource.id);
    const breadcrumbs: BreadcrumbItem[] = [this.resourceBreadcrumb(resource)];
    for (let resource of resources) {
      breadcrumbs.unshift(this.resourceBreadcrumb(resource));
    }
    breadcrumbs.unshift({
      label: this.i18n.tr(`resource_classes::${resource.resourceClass}//resources`),
      route: 'resources',
      params: {resourceClass: resource.resourceClass}
    });
    return breadcrumbs;
  }

  protected resourceBreadcrumb(resource: Resource): BreadcrumbItem {
    return {
      label: this.resourceDisplayStrategy.toView(resource, 'header'),
      route: 'resources/details',
      params: {id: resource.id},
      replace: true
    };
  }
}
