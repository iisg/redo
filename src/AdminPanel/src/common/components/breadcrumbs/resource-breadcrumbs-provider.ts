import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../../../resources/resource-repository";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {Resource} from "../../../resources/resource";
import {ResourceLabelValueConverter} from "../../../resources/details/resource-label-value-converter";

@autoinject
export class ResourceBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private resourceRepository: ResourceRepository, protected i18n: I18N,
              private resourceLabel: ResourceLabelValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    const resource = await this.resourceRepository.getTeaser(navigationInstruction.params.id);
    const resources = await this.resourceRepository.getHierarchy(resource.id);
    const breadcrumbs: BreadcrumbItem[] = [this.resourceBreadcrumb(resource)];
    for (let resource of resources) {
      breadcrumbs.unshift(this.resourceBreadcrumb(resource));
    }
    breadcrumbs.unshift({
      label: this.i18n.tr(`resource_classes::${resource.resourceClass}//resources`),
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
