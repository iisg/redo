import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Alert} from "common/dialog/alert";
import {I18N} from "aurelia-i18n";
import {EntitySerializer} from "common/dto/entity-serializer";
import {WorkflowTransition} from "../../workflows/workflow";
import {ResourceDisplayStrategyValueConverter} from "../../resources-config/resource-kind/display-strategies/resource-display-strategy";
import {computedFrom} from "aurelia-binding";
import {MetadataValue} from "../metadata-value";
import {ResourceClassTranslationValueConverter} from "../../common/value-converters/resource-class-translation-value-converter";
import {ContextResourceClass} from './../context/context-resource-class';

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  editing: boolean = false;
  selectedTransition: WorkflowTransition;
  hasChildren: boolean;
  private urlListener: Subscription;
  resourceDetailsTabs: any[] = [];
  currentTabId: string = '';

  constructor(private resourceRepository: ResourceRepository,
              private resourceDisplayStrategy: ResourceDisplayStrategyValueConverter,
              private resourceClassTranslation: ResourceClassTranslationValueConverter,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private alert: Alert,
              private i18n: I18N,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
    this.resourceDetailsTabs.forEach(tab => {
      this.ea.subscribe(`aurelia-plugins:tabs:tab-clicked:${tab.id}`, () => {
        this.resourceDetailsTabs.find(currentTab => currentTab.id == this.currentTabId).active = false;
        tab.active = true;
        this.currentTabId = tab.id;
      });
    });
  }

  unbind() {
    this.urlListener.dispose();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.resource = await this.resourceRepository.get(params.id);
    this.contextResourceClass.setCurrent(this.resource.resourceClass);
    const resources = await this.resourceRepository.getListQuery()
      .filterByParentId(this.resource.id)
      .get();
    this.hasChildren = resources.length > 0;
    const title = this.resourceDisplayStrategy.toView(this.resource, 'header');
    routeConfig.navModel.setTitle(title);
    this.activateTabs();
  }

  activateTabs() {
    if (this.allowAddChildResource) {
      this.resourceDetailsTabs.push({id: 'childResourceTab', label: this.i18n.tr('Child resources')});
      if (this.hasChildren) {
        this.currentTabId = 'childResourceTab';
      }
    }
    this.resourceDetailsTabs.push({id: 'metadataTab', label: this.i18n.tr('Metadata')});
    if (this.currentTabId === '') {
      this.currentTabId = 'metadataTab';
    }
    if (this.resource.kind.workflow) {
      this.resourceDetailsTabs.push({
        id: 'workflowTab',
        label: this.resourceClassTranslation.toView('Workflow', this.resource.resourceClass)
      });
    }
    this.resourceDetailsTabs.find(tab => tab.id == this.currentTabId).active = true;
  }

  @computedFrom('this.resource.kind.metadataList', 'this.resource.kind.metadataList.length')
  get allowAddChildResource(): boolean {
    const parentMetadata = this.resource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
    return !!parentMetadata.constraints.resourceKind.length;
  }

  toggleEditForm(transition?: WorkflowTransition) {
    if (!transition || this.resource.canApplyTransition(transition)) {
      // link can't be generated in the view with route-href because it is impossible to set replace:true there
      // see https://github.com/aurelia/templating-router/issues/54
      this.selectedTransition = transition ? transition : new WorkflowTransition();
      this.router.navigateToRoute('resources/details',
        {id: this.resource.id, action: this.editing ? undefined : 'edit', transitionId: this.selectedTransition.id}, {replace: true});

    }
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
    const parent: MetadataValue = this.resource.contents[SystemMetadata.PARENT.id][0];
    if (parent == undefined) {
      this.router.navigateToRoute('resources', {resourceClass: this.resource.resourceClass});
    } else {
      this.router.navigateToRoute('resources/details', {id: parent.value});
    }
  }
}
