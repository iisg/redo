import {RouteDefinition, RouteSettings} from "./route-types";

export function route(url: string|string[], moduleId: string, title: string, settings: RouteSettings = {}): RouteDefinition {
  return {
    route: url,
    name: moduleId,
    moduleId: moduleId,
    nav: true,
    title: title,
    settings: settings,
  };
}

export function nested(title: string, icon: string, children: RouteDefinition[]): RouteDefinition[] {
  for (let child of children) {
    child.settings.parentTitle = title;
    child.settings.parentIcon = icon;
  }
  return children;
}
