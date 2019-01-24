import {autoinject} from "aurelia-dependency-injection";
import {bindable, customElement} from "aurelia-templating";
import {MetadataChooser} from "resources-config/metadata/metadata-chooser";
import {Metadata} from "../metadata/metadata";
import {ResourceKind} from "./resource-kind";

@customElement('resource-kind-metadata-chooser')
@autoinject
export class ResourceKindMetadataChooser extends MetadataChooser {
  @bindable resourceKind: ResourceKind;

  filter = (metadata: Metadata) => {
    return this.notSystemMetadata(metadata) && this.metadataNotAlreadyInResourceKind(metadata);
  };

  notSystemMetadata(metadata: Metadata): boolean {
    return metadata.id > 0;
  }

  metadataNotAlreadyInResourceKind(metadata: Metadata, metadataList = this.resourceKind.metadataList): boolean {
    return this.resourceKind.metadataList.map(m => m.id).indexOf(metadata.id) === -1;
  }
}
