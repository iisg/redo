import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class MetadataList {
  metadataList: Metadata[];
  @bindable parentMetadata: Metadata;
  @bindable resourceClass: string;
  addFormOpened: boolean = false;
  progressBar: boolean;

  constructor(private metadataRepository: MetadataRepository, private entitySerializer: EntitySerializer) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    if (this.metadataList) {
      this.metadataList = [];
    }
    this.fetchMetadata();
  }

  parentMetadataChanged() {
    this.fetchMetadata();
  }

  private async fetchMetadata() {
    this.progressBar = true;
    this.metadataList = undefined;
    let query = this.metadataRepository.getListQuery();
    query = this.parentMetadata
      ? query.filterByParent(this.parentMetadata)
      : query.filterByResourceClasses(this.resourceClass).onlyTopLevel();
    this.metadataList = await query.get();
    this.progressBar = false;
  }

  isDragHandle(data: { evt: MouseEvent }) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }

  onOrderChanged() {
    this.metadataRepository.updateOrder(this.metadataList, this.resourceClass);
  }

  addNewMetadata(newMetadata: Metadata): Promise<any> {
    newMetadata.resourceClass = this.resourceClass;
    return this.metadataRepository.post(newMetadata)
      .then(metadata => this.metadataAdded(metadata));
  }

  metadataAdded(newMetadata: Metadata) {
    this.addFormOpened = false;
    this.metadataList.unshift(newMetadata);
  }

  saveEditedMetadata(metadata: Metadata, changedMetadata: Metadata): Promise<any> {
    const originalMetadata: Metadata = this.entitySerializer.clone(metadata);
    this.entitySerializer.hydrateClone(changedMetadata, metadata);
    metadata.pendingRequest = true;
    return this.metadataRepository.update(changedMetadata)
      .then(() => metadata.editing = false)
      .catch(() => this.entitySerializer.hydrateClone(originalMetadata, metadata))
      .finally(() => metadata.pendingRequest = false);
  }
}
