import {route, nested, flatten} from "./common/routes/route-utils";

const routes = flatten([
  route('', 'home', 'Overview', {icon: 'dashboard'}),
  route('resources', 'resources/resources', 'Resources', {icon: 'book'}),
  nested('Resources Setup', 'database', [
    route('metadata', 'resources-config/metadata/metadata-view', 'Metadata Kinds'),
    route('resource-kinds', 'resources-config/resource-kind/resource-kind-list', 'Resource Kinds'),
    route('workflows', 'workflows/workflows', 'Workflows'),
    route('languages', 'resources-config/language-config/language-list', 'Languages', {staticPermissions: ['LANGUAGES']})
  ]),
  nested('Users', 'users', [
    route('users', 'users/users', 'User list', {staticPermissions: ['USERS']}),
    route('roles', 'users/roles/user-roles', 'Roles', {staticPermissions: ['USERS']}),
  ]),
  route('about', 'about/about', 'About', {icon: 'info-circle'}),
]);

export default routes;
