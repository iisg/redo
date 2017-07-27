import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {Configure} from "aurelia-configuration";
import {route} from "../../routes/route-utils";

@autoinject
export class SiteMenu {
  navigation: any = [];

  constructor(public router: Router, private config: Configure) {
    this.addHomeNavigation();
    this.addResourceClassesNavigation(this.config.get('resource_classes'));
    this.addRemainingNavigations();
  }

  addHomeNavigation() {
    this.navigation.push(route('home', 'home', 'Tasks', {icon: 'dashboard'}));
  }

  addResourceClassesNavigation(resourceClasses: StringStringMap[]) {
    for (const value in resourceClasses) {
      const resourceClass = resourceClasses[value].toString();
      if (resourceClass === 'users') {
        this.navigation.push(route('users/users', 'users/users', resourceClass, {icon: 'users', dynamicResource: true}));
      } else {
        this.navigation.push(route('resources/resources',
          'resources/resources',
          resourceClass,
          {icon: 'book', params: {resourceClass}, dynamicResource: true})
        );
      }
      const parent =  {
        title: resourceClass,
        settings: {icon: 'database'},
        expanded: false,
        children: []
      };
      parent.children = [
        route('resources-config/metadata/metadata-view',
          'resources-config/metadata/metadata-view',
          'Metadata Kinds',
          {params: {resourceClass}, dynamicResource: true}
        ),
        route('resources-config/resource-kind/resource-kind-list',
          'resources-config/resource-kind/resource-kind-list',
          'Resource Kinds',
          {params: {resourceClass}, dynamicResource: true}
        ),
        route('workflows/workflows',
          'workflows/workflows',
          'Workflows',
          {params: {resourceClass}, dynamicResource: true}
        )
      ];
      if (resourceClass === 'users') {
        parent.children.push(route('users/roles/user-roles',
          'users/roles/user-roles',
          'Roles',
          {requiredRoles: ['ADMIN'], dynamicResource: true})
        );
      }
      this.navigation.push(parent);
    }
  }

  addRemainingNavigations() {
    this.navigation.push(route('resources-config/language-config/language-list',
      'resources-config/language-config/language-list',
      'Languages',
      {requiredRoles: ['ADMIN'], icon: 'language'})
    );
    this.navigation.push(route('about/about', 'about/about', 'About', {icon: 'info-circle'}));
  }
}
