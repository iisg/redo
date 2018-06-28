import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {Alert} from "common/dialog/alert";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {ResourceClassTranslationValueConverter} from "../../common/value-converters/resource-class-translation-value-converter";
import {ResourceDisplayStrategyValueConverter} from "../../resources-config/resource-kind/display-strategies/resource-display-strategy";
import {WorkflowTransition, WorkflowPlace} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ContextResourceClass} from "./../context/context-resource-class";
import {DetailsViewTabs} from "../../resources-config/metadata/details/details-view-tabs";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {
  resource: Resource;
  isFormOpened = false;
  isFormOpenedForGod: boolean;
  selectedTransition: WorkflowTransition;
  resultsPerPage: number;
  currentPageNumber: number;
  resourceDetailsTabs: DetailsViewTabs;
  numberOfChildren: number;
  isFiltering: boolean;
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
              private hasRole: HasRoleValueConverter) {
    this.resourceDetailsTabs = new DetailsViewTabs(this.ea, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => {
        this.isFormOpened = event.instruction.queryParams.action == 'edit';
        this.isFormOpenedForGod = !!event.instruction.queryParams['god']
          && this.hasRole.toView('ADMIN', this.resource.resourceClass);
      }
    );
  }

  unbind() {
    this.urlListener.dispose();
    this.resourceDetailsTabs.clear();
  }

  async activate(parameters: any, routeConfiguration: RouteConfig) {
    this.isFiltering = parameters.hasOwnProperty('contentsFilter');
    this.resource = await this.resourceRepository.get(parameters.id);
    this.contextResourceClass.setCurrent(this.resource.resourceClass);
    const resources = await this.resourceRepository.getListQuery()
      .filterByParentId(this.resource.id)
      .get();
    this.numberOfChildren = resources.total;
    const title = this.resourceDisplayStrategy.toView(this.resource, 'header');
    routeConfiguration.navModel.setTitle(title);
    this.activateTabs(parameters.tab);
  }

  activateTabs(activeTabId) {
    this.resourceDetailsTabs.clear();
    if (this.allowAddChildResource || this.numberOfChildren) {
      this.resourceDetailsTabs.addTab('children', () => `${this.i18n.tr('Child resources')} (${this.numberOfChildren})`);
    }
    this.resourceDetailsTabs
      .addTab('details', this.i18n.tr('Metadata'))
      .setDefaultTabId(this.numberOfChildren ? 'children' : 'details');
    if (this.resource.kind.workflow) {
      if (this.resource.kind.workflow) {
        this.resourceDetailsTabs.addTab('workflow', this.resourceClassTranslation.toView('Workflow', this.resource.resourceClass));
      }
    }
    if (this.resource.resourceClass == 'users') {
      const isUserResourceKind = this.resource.kind.metadataList.filter(m => m.id == SystemMetadata.GROUP_MEMBER.id).length > 0;
      if (isUserResourceKind) {
        if (this.hasRole.toView('ADMIN', 'users')) {
          this.resourceDetailsTabs.addTab('user-roles', this.i18n.tr('Roles'));
        }
      }
      else {
        this.resourceDetailsTabs.addTab('users-in-group', this.i18n.tr('Users'));
      }
    }
    this.resourceDetailsTabs.setActiveTabId(activeTabId);
  }

  @computedFrom('resource.kind.metadataList', 'resource.kind.metadataList.length')
  get allowAddChildResource(): boolean {
    const parentMetadata = this.resource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
    return !!parentMetadata.constraints.resourceKind.length;
  }

  @computedFrom("resource")
  get updateTransition(): WorkflowTransition {
    return this.resource.availableTransitions.filter(t => t.id == 'update')[0];
  }

  showTransitionForm(transition: WorkflowTransition) {
    this.selectedTransition = transition;
    this.updateUrl({editAction: true, skipValidation: false, triggerNavigation: true});
    // form is opened after navigation
  }

  showGodForm() {
    this.updateUrl({editAction: true, skipValidation: true, triggerNavigation: true});
  }

  hideForm() {
    this.selectedTransition = undefined;
    this.updateUrl({editAction: false, skipValidation: false, triggerNavigation: true});
    // form is closed after navigation
  }

  saveEditedResource(updatedResource: Resource,
                     transitionId: string,
                     newResourceKind: ResourceKind = undefined,
                     places: WorkflowPlace[] = []): Promise<Resource> {
    const originalResource = this.entitySerializer.clone(this.resource);
    $.extend(this.resource, updatedResource);
    return this.applyTransition(updatedResource, transitionId, newResourceKind, places).then(resourceData => {
      this.hideForm();
      return this.resource = resourceData;
    }).catch(() => $.extend(this.resource, originalResource));
  }

  private applyTransition(updatedResource: Resource,
                          transitionId: string,
                          newResourceKind: ResourceKind,
                          places: WorkflowPlace[]): Promise<Resource> {
    if (transitionId) {
      return this.resourceRepository.updateAndApplyTransition(updatedResource, transitionId);
    } else if (this.isFormOpenedForGod) {
      const placesIds = places.map(place => place.id);
      return this.resourceRepository.updateResourceWithNoValidation(updatedResource, newResourceKind.id, placesIds);
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

  private updateUrl(args: { editAction, skipValidation, triggerNavigation } = {
    editAction: this.isFormOpened,
    skipValidation: this.isFormOpenedForGod,
    triggerNavigation: false
  }) {
    const parameters = {};
    if (this.resourceDetailsTabs.activeTabId == 'children') {
      parameters['resourcesPerPage'] = this.resultsPerPage;
      parameters['currentPageNumber'] = this.currentPageNumber;
    }
    parameters['id'] = this.resource.id;
    if (args.editAction) {
      parameters['action'] = 'edit';
      parameters['tab'] = 'details';
      if (args.skipValidation) {
        parameters['god'] = true;
      }
    } else {
      parameters['tab'] = this.resourceDetailsTabs.activeTabId;
    }
    if (this.selectedTransition) {
      parameters['transitionId'] = this.selectedTransition.id;
    }
    this.router.navigateToRoute('resources/details', parameters, {trigger: args.triggerNavigation, replace: true});
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
