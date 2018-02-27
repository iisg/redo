import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../../../resources/resource-repository";
import {BreadcrumbItem} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {ResourceDisplayStrategyValueConverter} from "../../../resources-config/resource-kind/display-strategies/resource-display-strategy";
import {ResourceBreadcrumbsProvider} from "./resource-breadcrumbs-provider";
import {UserRepository} from "../../../users/user-repository";

@autoinject
export class UserBreadcrumbsProvider extends ResourceBreadcrumbsProvider {
  constructor(resourceRepository: ResourceRepository, i18n: I18N, resourceDisplayStrategy: ResourceDisplayStrategyValueConverter,
              private userRepository: UserRepository) {
    super(resourceRepository, i18n, resourceDisplayStrategy);
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    const user = await this.userRepository.get(navigationInstruction.params.id);
    const breadcrumbs: BreadcrumbItem[] = [this.resourceBreadcrumb(user.userData)];
    breadcrumbs.unshift({
      label: this.i18n.tr(`resource_classes::users//users`),
      route: 'users'
    });
    return breadcrumbs;
  }
}
