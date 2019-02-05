import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, RoutableComponentActivate, Router} from "aurelia-router";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {groupMetadata} from "../../../common/utils/metadata-utils";
import {DetailsViewTabs} from "../../metadata/details/details-view-tabs";
import {GroupMetadataList} from "../../metadata/metadata";
import {MetadataGroupRepository} from "../../metadata/metadata-group-repository";
import {ResourceRepository} from "../../../resources/resource-repository";

@autoinject
export class ResourceKindDetails implements RoutableComponentActivate {

  resourceKind: ResourceKind;
  editing: boolean = false;
  resourceKindDetailsTabs: DetailsViewTabs;
  metadataGroups: GroupMetadataList[];
  numberOfResources: number;
  private urlListener: Subscription;
  private resourceChildrenListener: Subscription;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private router: Router,
              private eventAggregator: EventAggregator,
              private i18n: I18N,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private contextResourceClass: ContextResourceClass,
              private metadataGroupRepository: MetadataGroupRepository,
              private resourceRepository: ResourceRepository) {
    this.resourceKindDetailsTabs = new DetailsViewTabs(this.eventAggregator, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.eventAggregator.subscribe('router:navigation:success', (event: { instruction: NavigationInstruction }) => {
      this.editing = event.instruction.queryParams.action == 'edit';
    });
    this.resourceChildrenListener = this.eventAggregator.subscribe('resourceChildrenAmount', (numberOfResources: number) => {
        this.updateResourceListTab(numberOfResources);
      }
    );
  }

  unbind() {
    this.resourceKindDetailsTabs.clear();
    this.urlListener.dispose();
    this.resourceChildrenListener.dispose();
  }

  updateResourceListTab(numberOfResources: number) {
    this.numberOfResources = numberOfResources;
    this.resourceKindDetailsTabs.updateLabels();
  }

  async activate(params: any) {
    this.resourceKind = await this.resourceKindRepository.get(params.id);
    const displayedMetadata = this.resourceKind.metadataList.filter(metadata => !!metadata.resourceClass);
    this.metadataGroups = groupMetadata(displayedMetadata, this.metadataGroupRepository.getIds());
    this.activateTabs(params.tab);
    this.contextResourceClass.setCurrent(this.resourceKind.resourceClass);
  }

  activateTabs(activeTabId: string) {
    this.resourceKindDetailsTabs.clear()
      .addTab('details', () => `${this.i18n.tr('Metadata')} (${this.resourceKind.metadataList.filter(m => !!m.resourceClass).length})`);
    this.resourceKindDetailsTabs.addTab(
      'resources',
      () => this.i18n.tr('resource_classes::' + this.resourceKind.resourceClass + '//resources')
        + (this.numberOfResources === undefined ? '' : ` (${this.numberOfResources})`)
    );
    if (this.resourceKind.workflow) {
      this.resourceKindDetailsTabs.addTab('workflow', this.i18n.tr('Workflow'));
    }
    this.resourceKindDetailsTabs.activateTab(activeTabId);
  }

  showEditForm() {
    this.updateUrl({editAction: true, triggerNavigation: true});
  }

  hideEditForm() {
    this.updateUrl({editAction: false, triggerNavigation: true});
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<any> {
    resourceKind.pendingRequest = true;
    return this.resourceKindRepository.put(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
      .then(() => this.hideEditForm())
      .finally(() => resourceKind.pendingRequest = false);
  }

  private updateUrl(args: { editAction, triggerNavigation } = {
    editAction: this.editing,
    triggerNavigation: false
  }) {
    const parameters = {};
    parameters['id'] = this.resourceKind.id;
    if (args.editAction) {
      parameters['action'] = 'edit';
    }
    parameters['tab'] = this.resourceKindDetailsTabs.activeTabId;
    this.router.navigateToRoute('resource-kinds/details', parameters, {trigger: args.triggerNavigation, replace: true});
    this.resourceKindDetailsTabs.updateLabels();
  }

  deleteResourceKind(): Promise<any> {
    return this.deleteEntityConfirmation.confirm('resourceKind', this.resourceKind.id)
      .then(() => this.resourceKind.pendingRequest = true)
      .then(() => this.resourceKindRepository.remove(this.resourceKind))
      .then(() => this.router.navigateToRoute('resource-kinds',
        {resourceClass: this.resourceKind.resourceClass},
        {replace: true}))
      .finally(() => this.resourceKind.pendingRequest = false);
  }
}
