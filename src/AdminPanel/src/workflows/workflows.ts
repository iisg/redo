import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, RouterConfiguration, Router} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class Workflows implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {route: '', name: 'workflows/list', moduleId: './workflow-list'},
      {route: '/:id', name: 'workflows/details', moduleId: './details/workflow-details'},
    ]);
  }
}
