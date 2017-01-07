import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";

@autoinject
export class MetadataList implements ComponentAttached {
  metadataList: Metadata[];
  addFormOpened: boolean = false;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached(): void {
    this.metadataRepository.getList().then(metadataList => this.metadataList = metadataList);
  }

  isDragHandle(data: {event: MouseEvent}) {
    return $(data.event.target).is('.drag-handle') || $(data.event.target).parents('.drag-handle').length > 0;
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
}
