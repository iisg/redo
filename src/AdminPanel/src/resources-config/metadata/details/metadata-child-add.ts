import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";

@autoinject
export class MetadataChildAdd implements ComponentAttached {
  @bindable parentMetadata: Metadata;
  @bindable resourceClass: string;
  @bindable saved: (value: { savedMetadata: Metadata }) => any;
  @bindable cancel: () => void;
  metadataList: Metadata[];
  parentMetadataChildren: Metadata[];
  baseMetadata: Metadata;
  notSelected: (metadata: Metadata) => boolean;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    Promise.all([
      this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .onlyTopLevel()
        .get(),
      this.metadataRepository.getListQuery().filterByParentId(this.parentMetadata.id).get()
    ]).then(results => {
      this.metadataList = results[0];
      this.parentMetadataChildren = results[1];
      this.notSelected = (metadata: Metadata) => {
        return this.parentMetadataChildren.map(m => m.baseId).indexOf(metadata.id) === -1;
      };
    });
  }

  addChildMetadata(parentId: number, baseId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata, baseId)
      .then(metadata => this.saved({savedMetadata: metadata}))
      .then(() => this.baseMetadata = undefined);
  }

  createChildMetadata(parentId: number, newChildMetadata: Metadata): Promise<Metadata> {
    return this.metadataRepository.saveChild(parentId, newChildMetadata)
      .then(metadata => this.saved({savedMetadata: metadata}));
  }
}
