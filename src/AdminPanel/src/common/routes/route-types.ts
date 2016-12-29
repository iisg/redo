export class RouteDefinition {
  route: string|string[];
  name: string;
  moduleId: string;
  nav: boolean;
  title: string;
  settings: {
    icon?: string;
    parentTitle?: string;
    parentIcon?: string;
  };
}

export class ActivatableNavItem extends RouteDefinition {
  isActive: boolean;
}

export interface NavItemWithChildren extends RouteDefinition {
  children: ActivatableNavItem[];
  expanded: boolean;
  toggle();
}
