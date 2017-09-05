import {Metadata} from "../metadata/metadata";
import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ResourceKind} from "./resource-kind";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceKindMetadataChooser implements ComponentAttached {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  async attached() {
    this.metadataList = await this.metadataRepository.getList();
  }
}
