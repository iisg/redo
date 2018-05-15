import {Metadata} from "../metadata/metadata";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceKindMetadataChooser {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;
  @bindable(twoWay) shouldRefreshResults: boolean;

  canBeAddedToResourceKind = (metadata: Metadata) => {
    return this.notSystemMetadata(metadata) && this.metadataNotAlreadyInResourceKind(metadata);
  }

  notSystemMetadata(metadata: Metadata): boolean {
    return metadata.id > 0;
  }

  metadataNotAlreadyInResourceKind(metadata: Metadata, metadataList = this.resourceKind.metadataList): boolean {
    return this.resourceKind.metadataList.map(m => m.id).indexOf(metadata.id) === -1;
  }
}
