import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";
import {deepCopy} from "common/utils/object-utils";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {removeValue} from "common/utils/array-utils";

@autoinject
export class MetadataList implements ComponentAttached {
  metadataList: Metadata[];
  @bindable parentMetadata: Metadata;
  addFormOpened: boolean = false;

  constructor(private metadataRepository: MetadataRepository, private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  attached() {
    if (!this.parentMetadata) {
      this.fetchMetadata();
    }
  }

  parentMetadataChanged(value, oldValue) {
    this.fetchMetadata();
  }

  private async fetchMetadata() {
    this.metadataList = undefined;
    this.metadataList = this.parentMetadata
      ? await this.metadataRepository.getByParent(this.parentMetadata)
      : await this.metadataRepository.getList();
  }

  isDragHandle(data: { evt: MouseEvent }) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }

  onOrderChanged() {
    this.metadataRepository.updateOrder(this.metadataList);
  }

  addNewMetadata(newMetadata: Metadata): Promise<any> {
    return this.metadataRepository.post(newMetadata)
      .then(metadata => this.metadataAdded(metadata));
  }

  metadataAdded(newMetadata: Metadata) {
    this.addFormOpened = false;
    this.metadataList.unshift(newMetadata);
  }

  saveEditedMetadata(metadata: Metadata, changedMetadata: Metadata): Promise<any> {
    const originalMetadata: Metadata = deepCopy(metadata);
    $.extend(metadata, changedMetadata);
    return this.metadataRepository.update(changedMetadata)
      .then(() => metadata.editing = false)
      .catch(() => $.extend(metadata, originalMetadata));
  }

  deleteMetadata(metadata: Metadata) {
    this.deleteEntityConfirmation.confirm('metadata', metadata.name)
      .then(() => metadata.pendingRequest = true)
      .then(() => this.metadataRepository.remove(metadata))
      .then(() => removeValue(this.metadataList, metadata))
      .finally(() => metadata.pendingRequest = false);
  }
}
