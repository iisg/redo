import {Metadata} from "../metadata/metadata";
import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {MetadataRepository} from "../metadata/metadata-repository";
import {ResourceKind} from "./resource-kind";

@autoinject
export class ResourceKindMetadataChooser implements ComponentAttached {
  @bindable resourceKind: ResourceKind;
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Metadata;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasMetadataToChoose: boolean;

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  async attached() {
    this.metadataList = await this.metadataRepository.getList();
  }
}
