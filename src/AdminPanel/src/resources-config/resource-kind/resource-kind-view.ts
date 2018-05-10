import {ConfiguresRouter, RouterConfiguration, Router} from "aurelia-router";
import {inlineView} from "aurelia-templating";

@inlineView("<template><router-view></router-view></template>")
export class ResourceKindView implements ConfiguresRouter {
  configureRouter(config: RouterConfiguration, router: Router): void {
    config.map([
      {route: '', name: 'resource-kinds/list', moduleId: './resource-kinds-list'},
      {route: '/details/:id', name: 'resource-kinds/details', moduleId: './details/resource-kind-details'},
      {route: '/../workflows/:id', name: 'workflows/details', moduleId: 'workflows/details/workflow-details'},
    ]);
  }
}
