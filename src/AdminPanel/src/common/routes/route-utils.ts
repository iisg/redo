import {RouteDefinition} from "./route-types";

export function route(url: string|string[], moduleId: string, title: string, icon?: string): RouteDefinition {
  return {
    route: url,
    name: moduleId,
    moduleId: moduleId,
    nav: true,
    title: title,
    settings: {
      icon: icon
    }
  };
}

export function nested(title: string, icon: string, children: RouteDefinition[]): RouteDefinition[] {
  for (let child of children) {
    child.settings.parentTitle = title;
    child.settings.parentIcon = icon;
  }
  return children;
}

// http://stackoverflow.com/a/10865042/878514
export function flatten<T>(arrayOfArrays: Array<T|T[]>): Array<T> {
  return [].concat.apply([], arrayOfArrays);
}
