export class RouteDefinition {
  route: string|string[];
  name: string;
  moduleId: string;
  title: string;
  settings: RouteSettings;
}

export interface NavItem extends RouteDefinition {
  isActive: boolean;
  children: NavItem[];
  expanded: boolean;
}

export interface RouteSettings {
  icon?: string;
  parentTitle?: string;
  parentIcon?: string;
  requiredRoles?: Array<string>;
  params?: StringStringMap;
  dynamicResource?: boolean;
}
