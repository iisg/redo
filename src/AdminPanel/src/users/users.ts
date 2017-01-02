import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, RouterConfiguration, Router} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class Users implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {route: '', name: 'users/list', moduleId: './user-list'},
      {route: '/:id', name: 'user/details', moduleId: './details/user-details'},
    ]);
  }
}
