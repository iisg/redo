import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {DeleteEntityConfirmation} from "../../../common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {computedFrom} from "aurelia-binding";
import {Configure} from "aurelia-configuration";

@autoinject
export class MetadataDetails implements RoutableComponentActivate {
  metadataChildrenList: Metadata[];
  metadata: Metadata;
  addFormOpened: boolean = false;
  editing: boolean = false;
  metadataDetailsTabs = [];
  currentTabId: string = '';
  numOfChildren: number;
  private listeners: Subscription[] = [];

  constructor(private metadataRepository: MetadataRepository,
              private i18n: I18N,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass,
              private config: Configure) {
  }

  bind() {
    this.listeners.push(this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit'));
    this.metadataDetailsTabs.forEach(tab => {
      this.listeners.push(this.ea.subscribe(`aurelia-plugins:tabs:tab-clicked:${tab.id}`, () => {
        this.metadataDetailsTabs.find(currentTab => currentTab.id == this.currentTabId).active = false;
        tab.active = true;
        this.currentTabId = tab.id;
        this.updateUrl();
      }));
    });
  }

  unbind() {
    this.listeners.forEach(listener => listener.dispose());
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.metadata = await this.metadataRepository.get(params.id);
    routeConfig.navModel.setTitle(this.i18n.tr('Metadata') + ` #${this.metadata.id}`);
    this.contextResourceClass.setCurrent(this.metadata.resourceClass);
    const metadata = await this.metadataRepository.getListQuery()
      .filterByParentId(this.metadata.id)
      .get();
    this.numOfChildren = metadata.length;
    this.currentTabId = params.currentTabId;
    this.activateTabs();
    if (this.currentTabId != params.currentTabId) {
      this.updateUrl();
    }
  }

  activateTabs() {
    this.metadataDetailsTabs.push({id: 'child-metadata-tab', label: `${this.i18n.tr('Submetadata kinds')} (${this.numOfChildren})`});
    this.metadataDetailsTabs.push({id: 'details-tab', label: this.i18n.tr('Details')});
    this.metadataDetailsTabs.push({id: 'constraints-tab', label: this.i18n.tr('Constraints')});
    this.currentTabId = this.currentTabId ? this.currentTabId : 'details-tab';
    this.metadataDetailsTabs.find(tab => tab.id == this.currentTabId).active = true;
  }

  @computedFrom('metadata.constraints', 'metadata.control')
  get constraintNames(): string[] {
    return this.config.get('control_constraints')[this.metadata.control];
  }

  childMetadataSaved(savedMetadata: Metadata) {
    this.metadataChildrenList.unshift(savedMetadata);
    this.addFormOpened = false;
  }

  deleteMetadata(): Promise<any> {
    return this.deleteEntityConfirmation.confirm('metadata', this.metadata.name)
      .then(() => this.metadata.pendingRequest = true)
      .then(() => this.metadataRepository.remove(this.metadata))
      .then(() => this.navigateToParentOrList())
      .finally(() => this.metadata.pendingRequest = false);
  }

  private navigateToParentOrList() {
    const parentId: number = this.metadata.parentId;
    if (parentId == undefined) {
      this.router.navigateToRoute('metadata', {resourceClass: this.metadata.resourceClass});
    } else {
      this.router.navigateToRoute('metadata/details', {id: parentId});
    }
  }

  toggleEditForm() {
    this.updateUrl(!this.editing);
    this.editing = !this.editing;
  }

  private updateUrl(editAction = this.editing) {
    const parameters = {};
    parameters['id'] = this.metadata.id;
    if (editAction) {
      parameters['action'] = 'edit';
      parameters['currentTabId'] = 'details-tab';
    } else {
      parameters['currentTabId'] = this.currentTabId;
    }
    this.router.navigateToRoute('metadata/details', parameters, {trigger: false, replace: true});
  }

  saveEditedMetadata(metadata: Metadata, changedMetadata: Metadata): Promise<any> {
    const originalMetadata: Metadata = this.entitySerializer.clone(metadata);
    this.entitySerializer.hydrateClone(changedMetadata, metadata);
    metadata.pendingRequest = true;
    return this.metadataRepository.update(changedMetadata)
      .then(() => this.editing = false)
      .catch(() => this.entitySerializer.hydrateClone(originalMetadata, metadata))
      .finally(() => metadata.pendingRequest = false);
  }
}
