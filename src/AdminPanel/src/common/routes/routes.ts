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

  new RouteGroup('resource-kinds', 'resource-kinds', 'resources-config/resource-kind', 'Resource Kinds', [
    new BaseRoute(':resourceClass', 'resource-kinds-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'resources'),
    new Route('details/:id', 'details', 'details/resource-kind-details')
      .withBreadcrumbsProvider('resourceKind')
  ]),

  new RouteGroup('workflows', 'workflows', 'workflows', 'Workflows', [
    new BaseRoute(':resourceClass', 'workflows-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'workflow'),
    new Route('details/:id', 'details', 'details/workflow-details')
      .withBreadcrumbsProvider('workflow'),
    new Route('new/:resourceClass', 'new', 'details/workflow-new').withBreadcrumbsProvider('workflow')
  ]),

  new Route('roles', 'roles', 'users/roles/user-roles').withMenuItem('Roles', NavRole.BOTTOM, 'roles'),

  new Route('languages', 'languages', 'resources-config/language-config/languages-list')
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
