import {AbstractRoute, BaseRoute, NavRole, Route, RouteGroup, RoutingBuilder} from "./routing-builder";

export const routes: AbstractRoute[] = new RoutingBuilder([
  new Route('', 'tasks', 'tasks/tasks').withMenuItem('Tasks', NavRole.TOP, 'tasks').requireRole('OPERATOR'),

  new RouteGroup('resources', 'resources', 'resources', 'Resources', [
    new BaseRoute(':resourceClass', 'list/resources-list').withMenuItem(NavRole.PER_RESOURCE_CLASS, 'book').requireRole('OPERATOR'),
    new Route('details/:id', 'details', 'details/resource-details')
      .setActivationStrategy('replace')
      .withBreadcrumbsProvider('resource')
      .requireRole('OPERATOR')
  ]),

  new RouteGroup('metadata', 'metadata', 'resources-config/metadata', 'Metadata Kinds', [
    new BaseRoute(':resourceClass', 'metadata-list').withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'metadata').requireRole('ADMIN'),
    new Route('details/:id', 'details', 'details/metadata-details').withBreadcrumbsProvider('metadata').requireRole('ADMIN')
  ]),

  new RouteGroup('resource-kinds', 'resource-kinds', 'resources-config/resource-kind', 'Resource Kinds', [
    new BaseRoute(':resourceClass', 'resource-kinds-list')
      .withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'resources')
      .requireRole('ADMIN'),
    new Route('details/:id', 'details', 'details/resource-kind-details').withBreadcrumbsProvider('resourceKind').requireRole('ADMIN')
  ]),

  new RouteGroup('workflows', 'workflows', 'workflows', 'Workflows', [
    new BaseRoute(':resourceClass', 'workflows-list').withMenuItem(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'workflow').requireRole('ADMIN'),
    new Route('details/:id', 'details', 'details/workflow-details').withBreadcrumbsProvider('workflow').requireRole('ADMIN'),
    new Route('new/:resourceClass', 'new', 'details/workflow-new').withBreadcrumbsProvider('workflow').requireRole('ADMIN')
  ]),

  new Route('languages', 'languages', 'resources-config/language-config/languages-list')
    .withMenuItem('Languages', NavRole.BOTTOM, 'languages')
    .requireRole('ADMIN'),

  new Route('audit', 'audit', 'audit/audit-page')
    .withMenuItem('Audit', NavRole.BOTTOM, 'scan-2')
    .requireRole('ADMIN'),

  new Route('about', 'about', 'about/about').withMenuItem('About', NavRole.BOTTOM, 'information'),
]).toArray();

// used in contexts where DI is available for better testability
export class RouteProvider {
  getRoutes(): AbstractRoute[] {
    return routes;
  }
}
