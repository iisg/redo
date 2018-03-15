import {NavigationInstruction, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {DeleteEntityConfirmation} from "../../../common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ContextResourceClass} from 'resources/context/context-resource-class';

@autoinject
export class MetadataDetails implements RoutableComponentActivate {
  metadataChildrenList: Metadata[];
  metadata: Metadata;
  addFormOpened: boolean = false;
  editing: boolean = false;
  urlListener: Subscription;

  constructor(private metadataRepository: MetadataRepository,
              private i18n: I18N,
              private router: Router,
              private ea: EventAggregator,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.editing = event.instruction.queryParams.action == 'edit');
  }

  unbind() {
    this.urlListener.dispose();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.metadata = await this.metadataRepository.get(params.id);
    routeConfig.navModel.setTitle(this.i18n.tr('Metadata') + ` #${this.metadata.id}`);
    this.contextResourceClass.setCurrent(this.metadata.resourceClass);
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
