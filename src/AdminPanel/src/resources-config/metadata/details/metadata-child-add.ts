import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class MetadataChildAdd implements ComponentAttached {
  @bindable saved: (value: { savedMetadata: Metadata }) => any;
  @bindable parentMetadata: Metadata;
  @bindable resourceClass: string;

  metadataList: Metadata[];
  parentMetadataChildren: Metadata[];
  baseMetadata: Metadata;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    let metadataList, parentMetadataChildren;
    Promise.all([
      this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .onlyTopLevel()
        .get()
        .then(metadata => metadataList = metadata),
      this.metadataRepository.getListQuery().filterByParent(this.parentMetadata).get().then(children => parentMetadataChildren = children)
    ]).then(() => {
      this.parentMetadataChildren = parentMetadataChildren;
      this.metadataList = metadataList;
    });
  }

  addChildMetadata(parentId: number, baseId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata, baseId).then(
      metadata => this.saved({savedMetadata: metadata})
    );
  }

  createChildMetadata(parentId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata).then(
      metadata => this.saved({savedMetadata: metadata})
    );
  }
}
