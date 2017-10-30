import {RoutableComponentActivate, RouteConfig, Router, NavigationInstruction} from "aurelia-router";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {deepCopy} from "common/utils/object-utils";
import {ResourceLabelValueConverter} from "./resource-label";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Alert} from "common/dialog/alert";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../resources-config/metadata/metadata";
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  allMetadata: Metadata[];
  editing: boolean = false;
  hasChildren: boolean;
  private urlListener: Subscription;

  constructor(private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private resourceLabel: ResourceLabelValueConverter,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private alert: Alert,
              private i18n: I18N) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  activate(params: any, routeConfig: RouteConfig): void {
    this.resourceRepository.get(params.id).then(resource => {
      this.resource = resource;
      this.allMetadata = this.resource.kind.metadataList;
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
    }).catch(() => $.extend(this.resource, originalResource));
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
      this.router.navigateToRoute('resources/list');
    } else {
      this.router.navigateToRoute('resources/details', {id: parentId});
    }
  }
}
