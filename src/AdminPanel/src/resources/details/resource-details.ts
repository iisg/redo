import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {Alert} from "common/dialog/alert";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {UserRoleChecker} from "../../common/authorization/user-role-checker";
import {ResourceClassTranslationValueConverter} from "../../common/value-converters/resource-class-translation-value-converter";
import {ResourceDisplayStrategyValueConverter} from "../../resources-config/resource-kind/display-strategies/resource-display-strategy";
import {SystemResourceKinds} from "../../resources-config/resource-kind/system-resource-kinds";
import {WorkflowTransition} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ContextResourceClass} from './../context/context-resource-class';
import {DetailsViewTabs} from "../../resources-config/metadata/details/details-view-tabs";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  editing = false;
  selectedTransition: WorkflowTransition;
  resultsPerPage: number;
  currentPageNumber: number;
  resourceDetailsTabs: DetailsViewTabs;
  numberOfChildren: number;
  private urlListener: Subscription;

  constructor(private resourceRepository: ResourceRepository,
              private resourceDisplayStrategy: ResourceDisplayStrategyValueConverter,
              private resourceClassTranslation: ResourceClassTranslationValueConverter,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private alert: Alert,
              private i18n: I18N,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass,
              private userRoleChecker: UserRoleChecker) {
    this.resourceDetailsTabs = new DetailsViewTabs(this.ea, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
    this.resourceDetailsTabs.clear();
  }

  async activate(parameters: any, routeConfiguration: RouteConfig) {
    this.resource = await this.resourceRepository.get(parameters.id);
    this.contextResourceClass.setCurrent(this.resource.resourceClass);
    const resources = await this.resourceRepository.getListQuery()
      .filterByParentId(this.resource.id)
      .get();
    this.numberOfChildren = resources.length;
    const title = this.resourceDisplayStrategy.toView(this.resource, 'header');
    routeConfiguration.navModel.setTitle(title);
    this.activateTabs(parameters.tab);
  }

  activateTabs(activeTabId) {
    if (this.allowAddChildResource || this.numberOfChildren) {
      this.resourceDetailsTabs.addTab({id: 'children', label: `${this.i18n.tr('Child resources')} (${this.numberOfChildren})`});
    }
    this.resourceDetailsTabs
      .addTab({id: 'details', label: this.i18n.tr('Metadata')})
      .setDefaultTabId(this.numberOfChildren ? 'children' : 'details');
    if (this.resource.kind.workflow) {
      this.resourceDetailsTabs.addTab({
        id: 'workflow',
        label: this.resourceClassTranslation.toView('Workflow', this.resource.resourceClass)
      });
    }
    if (this.resource.kind.id == SystemResourceKinds.USER_ID) {
      this.resourceDetailsTabs.addTab({
        id: 'user-groups',
        label: this.i18n.tr('Groups')
      });
      if (this.userRoleChecker.hasAll(['ADMIN'])) {
        this.resourceDetailsTabs.addTab({
          id: 'user-roles',
          label: this.i18n.tr('Roles')
        });
      }
    }
    this.resourceDetailsTabs.setActiveTabId(activeTabId);
  }

  @computedFrom('this.resource.kind.metadataList', 'this.resource.kind.metadataList.length')
  get allowAddChildResource(): boolean {
    const parentMetadata = this.resource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
    return !!parentMetadata.constraints.resourceKind.length;
  }

  toggleEditForm(triggerNavigation = true, transition?: WorkflowTransition) {
    if (!transition || this.resource.canApplyTransition(transition)) {
      // link can't be generated in the view with route-href because it is impossible to set replace:true there
      // see https://github.com/aurelia/templating-router/issues/54
      this.selectedTransition = transition ? transition : new WorkflowTransition();
      this.updateUrl(!this.editing || !!transition, triggerNavigation);
      if (!triggerNavigation) {
        this.editing = !this.editing;
      }
    }
  }

  saveEditedResource(updatedResource: Resource, transitionId: string): Promise<Resource> {
    const originalResource = this.entitySerializer.clone(this.resource);
    $.extend(this.resource, updatedResource);
    return this.applyTransition(updatedResource, transitionId).then(resourceData => {
      this.toggleEditForm();
      return this.resource = resourceData;
    }).catch(() => $.extend(this.resource, originalResource));
  }

  private applyTransition(updatedResource: Resource, transitionId: string): Promise<Resource> {
    if (transitionId) {
      return this.resourceRepository.updateAndApplyTransition(updatedResource, transitionId);
    }
    return this.resourceRepository.update(updatedResource);
  }

  remove() {
    if (this.numberOfChildren) {
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

  private updateUrl(editAction = this.editing, triggerNavigation = false) {
    const parameters = {};
    if (this.resourceDetailsTabs.activeTabId == 'children') {
      parameters['resourcesPerPage'] = this.resultsPerPage;
      parameters['currentPageNumber'] = this.currentPageNumber;
    }
    parameters['id'] = this.resource.id;
    if (editAction) {
      parameters['action'] = 'edit';
      parameters['tab'] = 'details';
    } else {
      parameters['tab'] = this.resourceDetailsTabs.activeTabId;
    }
    if (this.selectedTransition) {
      parameters['transitionId'] = this.selectedTransition.id;
    }
    this.router.navigateToRoute('resources/details', parameters, {trigger: triggerNavigation, replace: true});
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
