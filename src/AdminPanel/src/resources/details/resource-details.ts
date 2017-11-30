import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {ResourceLabelValueConverter} from "./resource-label";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Alert} from "common/dialog/alert";
import {I18N} from "aurelia-i18n";
import {EntitySerializer} from "common/dto/entity-serializer";
import {WorkflowTransition} from "../../workflows/workflow";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  editing: boolean = false;
  selectedTransition: WorkflowTransition;
  hasChildren: boolean;
  private urlListener: Subscription;

  constructor(private resourceRepository: ResourceRepository,
              private resourceLabel: ResourceLabelValueConverter,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private alert: Alert,
              private i18n: I18N,
              private entitySerializer: EntitySerializer) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.resource = await this.resourceRepository.get(params.id);
    const title = this.resourceLabel.toView(this.resource);
    routeConfig.navModel.setTitle(title);
  }

  toggleEditForm(transition?: WorkflowTransition) {
    // link can't be generated in the view with route-href because it is impossible to set replace:true there
    // see https://github.com/aurelia/templating-router/issues/54
    this.selectedTransition = transition ? transition : new WorkflowTransition();
    this.router.navigateToRoute('resources/details',
      {id: this.resource.id, action: this.editing ? undefined : 'edit', transitionId: this.selectedTransition.id}, {replace: true});
  }

  saveEditedResource(updatedResource: Resource, transitionId: string): Promise<Resource> {
    const originalResource = this.entitySerializer.clone(this.resource);
    $.extend(this.resource, updatedResource);
    return this.resourceRepository.update(updatedResource).then(resourceData => {
      if (transitionId) {
        return this.applyTransition(resourceData, transitionId);
      }
      return resourceData;
    }).then(resourceData => {
      this.toggleEditForm();
      return this.resource = resourceData;
    }).catch(() => $.extend(this.resource, originalResource));
  }

  applyTransition(resource: Resource, transitionId: string): Promise<Resource> {
    return this.resourceRepository.applyTransition(resource, transitionId);
  }

  remove() {
    if (this.hasChildren) {
      const title = this.i18n.tr('Resource has children');
      const text = this.i18n.tr('Delete or disown them first to delete this resource');
      this.alert.show({type: 'warning'}, title, text);
    } else {
      this.deleteEntityConfirmation.confirm('resource', this.resource.id)
        .then(() => this.resource.pendingRequest = true)
        .then(() => this.resourceRepository.remove(this.resource))
        .then(() => this.navigateToParentOrList())
        .finally(() => this.resource.pendingRequest = false);
    }
  }

  private navigateToParentOrList() {
    const parentId: number = this.resource.contents[SystemMetadata.PARENT.baseId][0];
    if (parentId == undefined) {
      this.router.navigateToRoute('resources/list', {resourceClass: this.resource.resourceClass});
    } else {
      this.router.navigateToRoute('resources/details', {id: parentId});
    }
  }
}
