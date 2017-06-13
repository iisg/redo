import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {bindingMode} from "aurelia-binding";

@autoinject
export class MetadataChildrenList {
  @bindable metadata: Metadata;
  @bindable({defaultBindingMode: bindingMode.twoWay}) metadataChildrenList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  async metadataChanged() {
    if (this.metadata) {
      this.metadataChildrenList = await this.metadataRepository.getChildren(this.metadata.id);
    } else {
      this.metadataChildrenList = undefined;
    }
  }
}
