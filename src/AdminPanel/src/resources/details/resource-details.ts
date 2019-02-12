import {computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {Alert} from "common/dialog/alert";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {AuditListFilters} from "../../audit/audit-list-filters";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {ResourceClassTranslationValueConverter} from "../../common/value-converters/resource-class-translation-value-converter";
import {DetailsViewTabs} from "../../resources-config/metadata/details/details-view-tabs";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {WorkflowPlace, WorkflowTransition} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ContextResourceClass} from "../context/context-resource-class";
import {ResourceLabelValueConverter} from "./resource-label-value-converter";
import {unescape} from "lodash";
import {Metadata} from "../../resources-config/metadata/metadata";
import {safeJsonParse} from "../../common/utils/object-utils";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {MetadataControl} from "../../resources-config/metadata/metadata-control";

@autoinject
export class ResourceDetails implements RoutableComponentActivate {

  @observable metadata: Metadata;
  resource: Resource;
  parentResource: Resource;
  isFormOpened = false;
  isFormOpenedForGod: boolean;
  selectedTransition: WorkflowTransition;
  resourceDetailsTabs: DetailsViewTabs;
  numberOfChildren: number;
  hasChildren: boolean;
  isFiltering: boolean;
  private urlListener: Subscription;
  private childrenListener: Subscription;
  private resourceFormOpenedListener: Subscription;

  resultsPerPage: number;
  currentPageNumber: number;

  filters: AuditListFilters;
  metadataList: Metadata[];

  constructor(private resourceRepository: ResourceRepository,
              private metadataRepository: MetadataRepository,
              private resourceLabel: ResourceLabelValueConverter,
              private resourceClassTranslation: ResourceClassTranslationValueConverter,
              private router: Router,
              private eventAggregator: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private alert: Alert,
              private i18n: I18N,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass,
              private hasRole: HasRoleValueConverter) {
    this.resourceDetailsTabs = new DetailsViewTabs(this.eventAggregator, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.eventAggregator.subscribe('router:navigation:success',
      (event: { instruction: NavigationInstruction }) => {
        this.isFormOpened = event.instruction.queryParams.action == 'edit';
        this.isFormOpenedForGod = !!event.instruction.queryParams['god']
          && this.hasRole.toView('ADMIN', this.resource.resourceClass);
        this.resourceDetailsTabs.setDisabled(this.isFormOpened || this.isFormOpenedForGod);
      }
    );
    this.childrenListener = this.eventAggregator.subscribe('resourceChildrenAmount', (resourceChildrenAmount: number) => {
      this.numberOfChildren = resourceChildrenAmount;
      this.resourceDetailsTabs.updateLabels();
      }
    );
    this.resourceFormOpenedListener = this.eventAggregator.subscribe('resourceFormOpened', (disabled: boolean) => {
        this.resourceDetailsTabs.setDisabled(disabled);
      }
    );
  }

  unbind() {
    this.urlListener.dispose();
    this.childrenListener.dispose();
    this.resourceFormOpenedListener.dispose();
    this.resourceDetailsTabs.clear();
  }

  async activate(parameters: any, routeConfiguration: RouteConfig) {
    this.isFiltering = parameters.hasOwnProperty('contentsFilter');
    this.resource = await this.resourceRepository.get(parameters.id);
    const parentMetadata = this.resource.contents[SystemMetadata.PARENT.id];
    const parentId = parentMetadata && parentMetadata[0] && parentMetadata[0].value;
    if (parentId) {
      try {
        this.parentResource = await this.resourceRepository.get(parentId, true);
      } catch (e) {
      }
    }
    const hasOperatorRole = this.hasRole.toView('OPERATOR', this.resource.resourceClass);
    if (!hasOperatorRole) {
      this.router.navigateToRoute('not-allowed');
    } else {
      this.hasChildren = this.resource.hasChildren;
      this.contextResourceClass.setCurrent(this.resource.resourceClass);
      const title = unescape(this.resourceLabel.toView(this.resource));
      routeConfiguration.navModel.setTitle(title);
      const contentsFilter = safeJsonParse(parameters['contentsFilter']) || {};
      this.metadataList = await this.metadataRepository.getListQuery()
        .filterByRequiredKindIds(this.resource.kind.id)
        .filterByControls(MetadataControl.RELATIONSHIP)
        .excludeIds(SystemMetadata.PARENT.id)
        .get();
      if (this.metadataList && this.metadataList.length) {
        if (contentsFilter) {
          const key = Object.keys(contentsFilter).find(key => +contentsFilter[key] === this.resource.id);
          this.metadata = key ? this.metadataList.find(metadata => metadata.id == +key) : this.metadataList[0];
        } else {
          this.metadata = this.metadataList[0];
        }
      }
      this.activateTabs(parameters.tab);
    }
  }

  activateTabs(activeTabId) {
    this.resourceDetailsTabs.clear();
    if (this.allowAddChildResource || this.hasChildren) {
      this.resourceDetailsTabs.addTab(
        'children',
        () => `${this.i18n.tr('Child resources')}` + (this.numberOfChildren === undefined ? '' : ` (${this.numberOfChildren})`)
      );
    }
    this.resourceDetailsTabs
      .addTab('details', this.i18n.tr('Metadata'))
      .setDefaultTabId(this.hasChildren ? 'children' : 'details');
    if (this.metadataList.length) {
      this.resourceDetailsTabs.addTab(
        'relationships',
        () =>
          `${this.i18n.tr('Relationships')} (${this.metadataList.length})`);
    }
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
    }
    if (this.hasRole.toView('ADMIN', this.resource.resourceClass)) {
      this.resourceDetailsTabs.addTab('audit', this.i18n.tr('Audit'));
    }
    this.resourceDetailsTabs.activateTab(activeTabId);
  }

  @computedFrom('resource.kind.metadataList', 'resource.kind.metadataList.length')
  get allowedResourceKindsByParent(): ResourceKind[] | number[] {
    const parentMetadata = this.resource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
    return parentMetadata.constraints.resourceKind;
  }

  @computedFrom('allowedResourceKindsByParent')
  get allowAddChildResource(): boolean {
    return !!this.allowedResourceKindsByParent.length;
  }

  @computedFrom("resource")
  get updateTransition(): WorkflowTransition {
    return this.resource.availableTransitions.filter(t => t.id == 'update')[0];
  }

  showTransitionForm(transition: WorkflowTransition) {
    this.selectedTransition = transition;
    this.isFormOpened = true;
    this.isFormOpenedForGod = false;
    this.resourceDetailsTabs.activateTab('details');
    this.resourceDetailsTabs.setDisabled(true);
  }

  showGodForm() {
    this.isFormOpened = true;
    this.isFormOpenedForGod = true;
    this.resourceDetailsTabs.activateTab('details');
    this.resourceDetailsTabs.setDisabled(true);
  }

  hideForm() {
    this.selectedTransition = undefined;
    this.isFormOpened = false;
    this.isFormOpenedForGod = false;
    this.resourceDetailsTabs.setDisabled(false);
  }

  saveEditedResource(updatedResource: Resource,
                     transitionId: string,
                     newResourceKind: ResourceKind = undefined,
                     places: WorkflowPlace[] = []): Promise<Resource> {
    const originalResource = this.entitySerializer.clone(this.resource);
    $.extend(this.resource, updatedResource);
    return this.applyTransition(updatedResource, transitionId, newResourceKind, places).then(resourceData => {
      this.hideForm();
      this.resource.kind = newResourceKind;
      return this.resource = resourceData;
    }).catch(() => $.extend(this.resource, originalResource));
  }

  private applyTransition(updatedResource: Resource,
                          transitionId: string,
                          newResourceKind: ResourceKind,
                          places: WorkflowPlace[]): Promise<Resource> {
    if (transitionId && transitionId !== 'update') {
      return this.resourceRepository.updateAndApplyTransition(updatedResource, transitionId);
    } else if (this.isFormOpenedForGod) {
      const placesIds = places.map(place => place.id);
      return this.resourceRepository.updateResourceWithNoValidation(updatedResource, newResourceKind.id, placesIds);
    }
    return this.resourceRepository.update(updatedResource);
  }

  cloneResource(resource: Resource): Promise<any> {
    return this.resourceRepository.post(resource).then(clonedResource => {
      this.router.navigateToRoute('resources/details', {id: clonedResource.id});
    });
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

  private updateUrl() {
    let parameters = {};
    if (this.resourceDetailsTabs.activeTabId == 'children') {
      parameters['resourcesPerPage'] = this.resultsPerPage;
      parameters['currentPageNumber'] = this.currentPageNumber;
    }
    parameters['id'] = this.resource.id;
    if (this.resourceDetailsTabs.activeTabId != 'details') {
      parameters['tab'] = this.resourceDetailsTabs.activeTabId;
    }
    if (this.resourceDetailsTabs.activeTabId == 'audit' && this.filters) {
      parameters = this.filters.toParams();
    }
    this.router.navigateToRoute('resources/details', parameters, {trigger: false, replace: true});
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
