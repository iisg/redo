import {RoutableComponentActivate, RouteConfig, Router, NavigationInstruction} from "aurelia-router";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {deepCopy} from "common/utils/object-utils";
import {ResourceLabelValueConverter} from "./resource-label";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  editing = false;
  private urlListener: Subscription;

  constructor(private resourceRepository: ResourceRepository,
              private resourceLabel: ResourceLabelValueConverter,
              private router: Router,
              private ea: EventAggregator) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  activate(params: any, routeConfig: RouteConfig): void {
    this.resourceRepository.get(params.id).then(resource => {
      this.resource = resource;
      const title = this.resourceLabel.toView(resource);
      routeConfig.navModel.setTitle(title);
    });
  }

  toggleEditForm() {
    // link can't be generated in the view with route-href because it is impossible to set replace:true there
    // see https://github.com/aurelia/templating-router/issues/54
    this.router.navigateToRoute('resources/details', {id: this.resource.id, action: this.editing ? undefined : 'edit'}, {replace: true});
  }

  saveEditedResource(updatedResource: Resource): Promise<Resource> {
    const originalResource = deepCopy(this.resource);
    $.extend(this.resource, updatedResource);
    return this.resourceRepository.update(updatedResource).then(resourceData => {
      this.toggleEditForm();
      return this.resource = resourceData;
    }) .catch(() => $.extend(this.resource, originalResource));
  }
}
