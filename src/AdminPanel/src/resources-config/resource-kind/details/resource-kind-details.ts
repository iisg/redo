import {Subscription, EventAggregator} from 'aurelia-event-aggregator';
import {ResourceKindRepository} from 'resources-config/resource-kind/resource-kind-repository';
import {ResourceKind} from 'resources-config/resource-kind/resource-kind';
import {RoutableComponentActivate, RouteConfig, Router, NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourceKindDetails implements RoutableComponentActivate {

  resourceKind: ResourceKind;
  editing: boolean = false;
  resourceKindDetailsTabs: any[] = [];
  currentTabId: string = '';

  private urlListener: Subscription;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private router: Router,
              private ea: EventAggregator,
              private i18n: I18N,
              private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success", (event: {instruction: NavigationInstruction}) => {
      this.editing = (event.instruction.queryParams.action == 'edit');
    });
    this.resourceKindDetailsTabs.forEach(tab => {
      this.ea.subscribe(`aurelia-plugins:tabs:tab-clicked:${tab.id}`, () => {
        this.resourceKindDetailsTabs.find(currentTab => currentTab.id == this.currentTabId).active = false;
        tab.active = true;
        this.currentTabId = tab.id;
      });
    });
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.resourceKind = await this.resourceKindRepository.get(params.id);
    this.activateTabs();
  }

  activateTabs() {
    this.resourceKindDetailsTabs.push({id: 'metadata-tab', label: this.i18n.tr('Metadata')});
    if (this.resourceKind.workflow) {
      this.resourceKindDetailsTabs.push({id: 'workflow-tab', label: this.i18n.tr('Workflow')});
    }
    this.currentTabId = 'metadata-tab';
    this.resourceKindDetailsTabs.find(tab => tab.id == this.currentTabId).active = true;
  }

  toggleEditForm(triggerNavigation = true) {
    // link can't be generated in the view with route-href because it is impossible to set replace:true there
    // see https://github.com/aurelia/templating-router/issues/54
    this.router.navigateToRoute('resource-kinds/details',
      {id: this.resourceKind.id, action: this.editing ? undefined : 'edit'},
      {trigger: triggerNavigation, replace: true});
    if (!triggerNavigation) {
      this.editing = !this.editing;
    }
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<any> {
    resourceKind.pendingRequest = true;
    return this.resourceKindRepository.update(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
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
