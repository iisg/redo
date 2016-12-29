import {route, nested, flatten} from "./common/routes/route-utils";

const routes = flatten([
  route('', 'home', 'Przegląd', 'dashboard'),
  nested('Konfiguracja zasobów', 'database', [
    route('metadane', 'resources-config/metadata/metadata-list', 'Metadane'),
    route('rodzaje-zasobow', 'resources-config/resource-kind/resource-kind-list', 'Rodzaje zasobów'),
    route('jezyki', 'resources-config/language-config/language-list', 'Języki')
  ]),
  route('o-systemie', 'about/about', 'O systemie', 'info-circle'),
]);

export default routes;
