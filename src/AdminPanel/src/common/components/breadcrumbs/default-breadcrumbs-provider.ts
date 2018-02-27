import {NavigationInstruction} from "aurelia-router";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";

export class DefaultBreadcrumbsProvider implements BreadcrumbsProvider {
  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    return [];
  }
}
