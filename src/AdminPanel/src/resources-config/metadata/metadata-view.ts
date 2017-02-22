import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, RouterConfiguration, Router} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class MetadataView implements ConfiguresRouter {
  router: Router;

  configureRouter(config: RouterConfiguration, router: Router): void {
    this.router = router;
    config.map([
      {route: '', name: 'metadata/list', moduleId: './metadata-list'},
      {route: '/:id', name: 'metadata/details', moduleId: './details/metadata-details'},
    ]);
  }
}
