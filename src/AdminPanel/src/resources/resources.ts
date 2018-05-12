import {inlineView} from "aurelia-templating";
import {activationStrategy, ConfiguresRouter, Router, RouterConfiguration} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class Resources implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {
        route: '',
        name: 'resources/list',
        moduleId: './list/resources-list'
      },
      {
        route: '/:id',
        name: 'resources/details',
        moduleId: './details/resource-details',
        activationStrategy: activationStrategy.replace
      }
    ]);
  }
}
