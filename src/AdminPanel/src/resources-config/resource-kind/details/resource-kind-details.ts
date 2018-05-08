import {EventAggregator, Subscription} from 'aurelia-event-aggregator';
import {ResourceKindRepository} from 'resources-config/resource-kind/resource-kind-repository';
import {ResourceKind} from 'resources-config/resource-kind/resource-kind';
import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {I18N} from "aurelia-i18n";
import {ContextResourceClass} from 'resources/context/context-resource-class';
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

  async activate(params: any, routeConfig: RouteConfig) {
    this.resourceKind = await this.resourceKindRepository.get(params.id);
    this.activateTabs(params.tab);
    this.contextResourceClass.setCurrent(this.resourceKind.resourceClass);
  }

  activateTabs(activeTabId: string) {
    // remove parent metadata from metadata length
    if (this.resourceKindDetailsTabs.length === 0) {
      const metadataListLength = this.resourceKind.metadataList.length - 1;
      this.resourceKindDetailsTabs.addTab({id: 'details', label: `${this.i18n.tr('Metadata')} (${metadataListLength})`});
    }
    if (this.resourceKind.workflow && !this.resourceKindDetailsTabs.tabExists('workflow')) {
      this.resourceKindDetailsTabs.addTab({id: 'workflow', label: this.i18n.tr('Workflow')});
    }
    this.resourceKindDetailsTabs.setActiveTabId(activeTabId);
  }

  private updateUrl() {
    const parameters = {};
    parameters['id'] = this.resourceKind.id;
    if (this.editing) {
      parameters['action'] = 'edit';
      parameters['tab'] = 'details';
    } else {
      parameters['tab'] = this.resourceKindDetailsTabs.activeTabId;
    }
    this.router.navigateToRoute('resource-kinds/details', parameters, {replace: true});
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
