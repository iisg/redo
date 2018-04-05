import {AbstractRoute, BaseRoute, NavRole, Route, RouteGroup, RoutingBuilder} from "./routing-builder";

export const routes: AbstractRoute[] = new RoutingBuilder([
  new Route('', 'tasks', 'tasks/tasks').withMenuItem('Tasks', NavRole.TOP, 'tasks'),

  new RouteGroup('resources', 'resources', 'resources', 'Resources', [
    new BaseRoute(':resourceClass', 'resources-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS, 'book'),
    new Route('details/:id', 'details', 'details/resource-details')
      .setActivationStrategy('replace')
      .withBreadcrumbsProvider('resource')
  ]),

  new RouteGroup('metadata', 'metadata', 'resources-config/metadata', 'Metadata Kinds', [
    new BaseRoute(':resourceClass', 'metadata-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'metadata'),
    new Route('details/:id', 'details', 'details/metadata-details')
      .withBreadcrumbsProvider('metadata')
  ]),

  new Route('resource-kinds/:resourceClass', 'resource-kinds', 'resources-config/resource-kind/resource-kind-list')
    .withMenuItem('Resource Kinds', NavRole.PER_RESOURCE_CLASS_SECONDARY, 'resources'),

  new RouteGroup('workflows', 'workflows', 'workflows', 'Workflows', [
    new BaseRoute(':resourceClass', 'workflow-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'workflow'),
    new Route('details/:id', 'details', 'details/workflow-details')
      .withBreadcrumbsProvider('workflow'),
    new Route('new/:resourceClass', 'new', 'details/workflow-new').withBreadcrumbsProvider('workflow')
  ]),

  new Route('roles', 'roles', 'users/roles/user-roles').withMenuItem('Roles', NavRole.BOTTOM, 'roles'),

  new Route('languages', 'languages', 'resources-config/language-config/language-list')
    .withMenuItem('Languages', NavRole.BOTTOM, 'languages'),

  new Route('audit', 'audit', 'audit/audit-page').withMenuItem('Audit', NavRole.BOTTOM, 'scan-2'),

  new Route('about', 'about', 'about/about').withMenuItem('About', NavRole.BOTTOM, 'information'),
]).toArray();

// used in contexts where DI is available for better testability
export class RouteProvider {
  getRoutes(): AbstractRoute[] {
    return routes;
  }
}
