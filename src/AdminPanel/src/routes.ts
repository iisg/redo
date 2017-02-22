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
  route('users', 'users/users', 'Users', {icon: 'user', staticPermissions: ['USERS']}),
  route('about', 'about/about', 'About', {icon: 'info-circle'}),
]);

export default routes;
