import {autoinject} from "aurelia-dependency-injection";
import {RouterConfiguration, ConfiguresRouter} from "aurelia-router";

@autoinject
export class Home implements ConfiguresRouter {
  configureRouter(config: RouterConfiguration): void {
    config.map([
      {route: '', name: 'home', moduleId: 'tasks/tasks'},
      {route: 'resources/:id', name: 'resources/details', moduleId: 'resources/details/resource-details'},
      {route: 'users/:id', name: 'users/details', moduleId: 'users/details/user-details'},
    ]);
  }
}
