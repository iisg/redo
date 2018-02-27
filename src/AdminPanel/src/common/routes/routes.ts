import {AbstractRoute, BaseRoute, NavRole, Route, RouteGroup, RoutingBuilder} from "./routing-builder";

export const routes: AbstractRoute[] = new RoutingBuilder([
  new Route('', 'tasks', 'tasks/tasks').withMenuItem('Tasks', NavRole.TOP, 'check-square-o'),

  new RouteGroup('resources', 'resources', 'resources', 'Resources', [
    new BaseRoute(':resourceClass', 'resources-list').withMenuItem(NavRole.PER_RESOURCE_CLASS, 'book'),
    new Route('details/:id', 'details', 'details/resource-details')
      .setActivationStrategy('replace').withBreadcrumbsProvider('resource')
  ]),

  new RouteGroup('metadata', 'metadata', 'resources-config/metadata', 'Metadata Kinds', [
    new BaseRoute(':resourceClass', 'metadata-list').withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY),
    new Route('details/:id', 'details', 'details/metadata-details').withBreadcrumbsProvider('metadata'),
  ]),

  new Route('resource-kinds/:resourceClass', 'resource-kinds', 'resources-config/resource-kind/resource-kind-list')
    .withMenuItem('Resource Kinds', NavRole.PER_RESOURCE_CLASS_SECONDARY),

  new RouteGroup('workflows', 'workflows', 'workflows', 'Workflows', [
    new BaseRoute(':resourceClass', 'workflow-list').withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY),
    new Route('details/:id', 'details', 'details/workflow-details').withBreadcrumbsProvider('workflow'),
    new Route('new/:resourceClass', 'new', 'details/workflow-new').withBreadcrumbsProvider('workflow'),
  ]),

  new RouteGroup('users', 'users', 'users', 'Users', [
    new BaseRoute('', 'user-list').withMenuItem(NavRole.PER_RESOURCE_CLASS, 'users', 'users'),
    new Route('roles', 'roles', 'roles/user-roles').withMenuItem('Roles', NavRole.PER_RESOURCE_CLASS_SECONDARY, undefined, 'users'),
    new Route('details/:id', 'details', 'details/user-details').withBreadcrumbsProvider('user')
  ]),

  new Route('languages', 'languages', 'resources-config/language-config/language-list')
    .withMenuItem('Languages', NavRole.BOTTOM, 'language'),
  new Route('about', 'about', 'about/about') .withMenuItem('About', NavRole.BOTTOM, 'info-circle'),
]).toArray();

// used in contexts where DI is available for better testability
export class RouteProvider {
  getRoutes(): AbstractRoute[] {
    return routes;
  }
}
