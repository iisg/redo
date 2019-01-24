import {inlineView} from "aurelia-templating";
import {ConfiguresRouter, Router, RouterConfiguration} from "aurelia-router";

@inlineView("<template><router-view></router-view></template>")
export class MetadataView implements ConfiguresRouter {
  configureRouter(config: RouterConfiguration, router: Router): void {
    config.map([
      {route: '', name: 'metadata/list', moduleId: './metadata-list'},
      {route: '/:id', name: 'metadata/details', moduleId: './details/metadata-details'},
      {route: '/../workflows/:id', name: 'workflows/details', moduleId: 'workflows/details/workflow-details'},
    ]);
  }
}
