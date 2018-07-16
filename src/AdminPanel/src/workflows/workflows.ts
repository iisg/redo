import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, Router, RouterConfiguration} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class Workflows implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {route: '', name: 'workflows/list', moduleId: './workflows-list'},
      {route: '/new', name: 'workflows/new', moduleId: './details/workflow-form'},
      {route: '/:id', name: 'workflows/details', moduleId: './details/workflow-details'},
    ]);
  }
}
