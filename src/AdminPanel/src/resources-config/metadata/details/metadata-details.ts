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
import {ResourceKindRepository} from "../../resource-kind/resource-kind-repository";
import {ResourceKind} from "../../resource-kind/resource-kind";
import {DetailsViewTabs} from "./details-view-tabs";

@autoinject
export class MetadataDetails implements RoutableComponentActivate {
  metadataChildrenList: Metadata[];
  metadata: Metadata;
  addFormOpened: boolean = false;
  editing: boolean = false;
  metadataDetailsTabs: DetailsViewTabs;
  numOfChildren: number;
  private urlListener: Subscription;
  resourceKindList: ResourceKind[];

  constructor(private metadataRepository: MetadataRepository,
              private resourceKindRepository: ResourceKindRepository,
              private i18n: I18N,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass,
              private config: Configure) {
    this.metadataDetailsTabs = new DetailsViewTabs(this.ea, () => this.updateUrl());
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
    this.metadataDetailsTabs.clear();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.metadata = await this.metadataRepository.get(params.id);
    routeConfig.navModel.setTitle(this.i18n.tr('Metadata') + ` #${this.metadata.id}`);
    this.contextResourceClass.setCurrent(this.metadata.resourceClass);
    const metadata = await this.metadataRepository.getListQuery()
      .filterByParentId(this.metadata.id)
      .get();
    this.resourceKindList = await this.resourceKindRepository.getListQuery().filterByMetadataId(this.metadata.id).get();
    this.numOfChildren = metadata.length;
    this.buildTabs(params.tab);
  }

  private buildTabs(activeTabId: string) {
    this.metadataDetailsTabs
      .addTab({id: 'details', label: this.i18n.tr('Details')})
      .addTab({id: 'child-metadata', label: `${this.i18n.tr('Submetadata kinds')} (${this.numOfChildren})`})
      .addTab({id: 'constraints', label: this.i18n.tr('Constraints')})
      .addTab({
        id: 'resource-kinds',
        label: `${this.i18n.tr('resource_classes::' + this.metadata.resourceClass + '//resource-kinds')} (${this.resourceKindList.length})`
      })
      .setActiveTabId(activeTabId);
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
    this.editing = !this.editing;
    this.updateUrl();
  }

  private updateUrl() {
    const parameters = {};
    parameters['id'] = this.metadata.id;
    if (this.editing) {
      parameters['action'] = 'edit';
      parameters['tab'] = 'details';
    } else {
      parameters['tab'] = this.metadataDetailsTabs.activeTabId;
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
