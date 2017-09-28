import {route, nested} from "./common/routes/route-utils";
import {flatten} from "./common/utils/array-utils";

const routes = flatten([
  route('', 'home', 'Overview', {icon: 'dashboard'}),
  route('resources', 'resources/resources', 'Resources', {icon: 'book'}),
  nested('Resources Setup', 'database', [
    route('metadata', 'resources-config/metadata/metadata-view', 'Metadata Kinds'),
    route('resource-kinds', 'resources-config/resource-kind/resource-kind-view', 'Resource Kinds'),
    route('workflows', 'workflows/workflows', 'Workflows'),
    route('languages', 'resources-config/language-config/language-list', 'Languages', {requiredRoles: ['ADMIN']})
  ]),
  nested('Users', 'users', [
    route('users', 'users/users', 'User list'),
    route('roles', 'users/roles/user-roles', 'Roles', {requiredRoles: ['ADMIN']}),
  ]),
  route('about', 'about/about', 'About', {icon: 'info-circle'}),
]);

export default routes;
