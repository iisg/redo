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

  addNewMetadata(newMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.post(newMetadata).then(metadata => {
      this.addFormOpened = false;
      this.metadataList.push(metadata);
      return metadata;
    });
  }
}
