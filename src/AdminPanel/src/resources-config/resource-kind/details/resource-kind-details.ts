import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, RoutableComponentActivate, Router} from "aurelia-router";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {DetailsViewTabs} from "../../metadata/details/details-view-tabs";

@autoinject
export class ResourceKindDetails implements RoutableComponentActivate {
  resourceKind: ResourceKind;
  editing: boolean = false;
  resourceKindDetailsTabs: DetailsViewTabs;
  private urlListener: Subscription;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private router: Router,
              private ea: EventAggregator,
              private i18n: I18N,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private contextResourceClass: ContextResourceClass) {
    this.resourceKindDetailsTabs = new DetailsViewTabs(this.ea, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success", (event: { instruction: NavigationInstruction }) => {
      this.editing = (event.instruction.queryParams.action == 'edit');
    });
  }

  unbind() {
    this.resourceKindDetailsTabs.clear();
    this.urlListener.dispose();
  }

  async activate(params: any) {
    this.resourceKind = await this.resourceKindRepository.get(params.id);
    this.activateTabs(params.tab);
    this.contextResourceClass.setCurrent(this.resourceKind.resourceClass);
  }

  activateTabs(activeTabId: string) {
    this.resourceKindDetailsTabs.clear()
      .addTab('details', () => `${this.i18n.tr('Metadata')} (${this.resourceKind.metadataList.filter(m => !!m.resourceClass).length})`);
    if (this.resourceKind.workflow) {
      this.resourceKindDetailsTabs.addTab('workflow', this.i18n.tr('Workflow'));
    }
    this.resourceKindDetailsTabs.setActiveTabId(activeTabId);
  }

  private updateUrl() {
    const parameters = {};
    parameters['id'] = this.resourceKind.id;
    if (this.editing) {
      parameters['action'] = 'edit';
    }
    parameters['tab'] = this.resourceKindDetailsTabs.activeTabId;
    this.router.navigateToRoute('resource-kinds/details', parameters, {replace: true});
    this.resourceKindDetailsTabs.updateLabels();
  }

  toggleEditForm() {
    this.editing = !this.editing;
    this.updateUrl();
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<any> {
    resourceKind.pendingRequest = true;
    return this.resourceKindRepository.put(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
      .then(() => this.toggleEditForm())
      .finally(() => resourceKind.pendingRequest = false);
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
