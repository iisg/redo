import {Metadata} from "../metadata/metadata";
import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode, computedFrom} from "aurelia-binding";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ResourceKind} from "./resource-kind";

@autoinject
export class ResourceKindMetadataChooser implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: Metadata;

  @bindable resourceKind: ResourceKind;

  dropdown: Element;

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    this.metadataRepository.getList().then((metadataList) => {
      this.metadataList = metadataList;
    });
  }

  @computedFrom("metadataList.length", "resourceKind.metadataList.length")
  get notUsedMetadata() {
    let usedIds = this.resourceKind ? this.resourceKind.metadataList.map(metadata => metadata.base.id) : [];
    return this.metadataList ? this.metadataList.filter(metadata => usedIds.indexOf(metadata.id) < 0) : [];
  }
}
