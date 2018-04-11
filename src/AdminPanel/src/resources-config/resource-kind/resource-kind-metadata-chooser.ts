import {Metadata} from "../metadata/metadata";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {twoWay} from "common/components/binding-mode";
import {SystemMetadata} from "../metadata/system-metadata";

@autoinject
export class ResourceKindMetadataChooser {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;

  canBeAddedToResourceKind = (metadata: Metadata) => {
    return this.notParentMetadata(metadata) && this.metadataNotAlreadyInResourceKind(metadata);
  };

  notParentMetadata(metadata: Metadata): boolean {
    return metadata.id != SystemMetadata.PARENT.id;
  }

  metadataNotAlreadyInResourceKind(metadata: Metadata): boolean {
    return this.resourceKind.metadataList.map(m => m.id).indexOf(metadata.id) === -1;
  }
}
