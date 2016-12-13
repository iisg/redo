import {route, nested, flatten} from "./common/routes/route-utils";

const routes = flatten([
  route('', 'home', 'Overview', 'dashboard'),
  route('resources', 'resources/resources-list', 'Resources', 'book'),
  nested('Resources Setup', 'database', [
    route('metadata', 'resources-config/metadata/metadata-list', 'Metadata'),
    route('resource-kinds', 'resources-config/resource-kind/resource-kind-list', 'Resource Kinds'),
    route('languages', 'resources-config/language-config/language-list', 'Languages')
  ]),
  route('about', 'about/about', 'About', 'info-circle'),
]);

export default routes;
