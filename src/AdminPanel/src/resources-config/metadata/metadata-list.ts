import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";
import {deepCopy} from "../../common/utils/object-utils";

@autoinject
export class MetadataList implements ComponentAttached {
  metadataList: Metadata[];
  addFormOpened: boolean = false;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached(): void {
    this.metadataRepository.getList().then(metadataList => this.metadataList = metadataList);
  }

  isDragHandle(data: {evt: MouseEvent}) {
    return $(data.evt.target).is('.drag-handle') || $(data.evt.target).parents('.drag-handle').length > 0;
  }

  onOrderChanged() {
    this.metadataRepository.updateOrder(this.metadataList);
  }

  addNewMetadata(newMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.post(newMetadata).then(metadata => {
      this.addFormOpened = false;
      this.metadataList.unshift(metadata);
      return metadata;
    });
  }

  saveEditedMetadata(metadata: Metadata, changedMetadata: Metadata): Promise<Metadata> {
    const originalMetadata = deepCopy(metadata);
    $.extend(metadata, changedMetadata);
    return this.metadataRepository.update(changedMetadata).catch(() => $.extend(metadata, originalMetadata));
  }
}
