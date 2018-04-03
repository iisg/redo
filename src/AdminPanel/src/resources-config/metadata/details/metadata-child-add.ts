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
  notAlreadyInParent;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    Promise.all([
      this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .onlyTopLevel()
        .get(),
      this.metadataRepository.getListQuery().filterByParent(this.parentMetadata).get()
    ]).then(results => {
      this.metadataList = results[0];
      this.parentMetadataChildren = results[1];
      this.notAlreadyInParent = (metadata: Metadata) => {
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
