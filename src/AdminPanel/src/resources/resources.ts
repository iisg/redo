import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, RouterConfiguration, Router, activationStrategy} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class Resources implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {
        route: '',
        name: 'resources/list',
        moduleId: './resources-list'
      },
      {
        route: '/:id',
        name: 'resources/details',
        moduleId: './details/resource-details',
        activationStrategy: activationStrategy.replace
      },
      {
        route: '/../users/:id',
        name: 'users/details',
        moduleId: 'users/details/user-details'
      },
    ]);
  }
}
