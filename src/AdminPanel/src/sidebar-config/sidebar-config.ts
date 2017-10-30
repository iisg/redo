import {autoinject} from "aurelia-dependency-injection";
import {route} from "../common/routes/route-utils";

@autoinject
export class SidebarConfig {

  public getRoutes() {
    return [
      route('', 'home', 'Tasks'),
      route('resources', 'resources/resources', 'Resources'),
      route('metadata/:resourceClass', 'resources-config/metadata/metadata-view', 'Metadata Kinds'),
      route('resource-kinds/:resourceClass', 'resources-config/resource-kind/resource-kind-list', 'Resource Kinds'),
      route('workflows/:resourceClass', 'workflows/workflows', 'Workflows'),
      route('roles', 'users/roles/user-roles', 'Roles'),
      route('users', 'users/users', 'Users'),
      route('languages', 'resources-config/language-config/language-list', 'Languages'),
      route('about', 'about/about', 'About')
    ];
  }
}
