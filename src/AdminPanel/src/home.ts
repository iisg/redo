import {autoinject} from "aurelia-dependency-injection";
import {Router, RouterConfiguration, ConfiguresRouter} from "aurelia-router";

@autoinject
export class Home implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {
        route: '',
        name: 'home',
        moduleId: 'assignments/assignments'
      },
      {
        route: '/../resources/:id',
        name: 'resources/details',
        moduleId: 'resources/details/resource-details',
      },
    ]);
  }
}
