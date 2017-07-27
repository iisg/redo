import {RouteDefinition, RouteSettings} from "./route-types";

export function route(url: string|string[], moduleId: string, title: string, settings: RouteSettings = {}): RouteDefinition {
  return {
    route: url,
    name: moduleId,
    moduleId: moduleId,
    title: title,
    settings: settings
  };
}
