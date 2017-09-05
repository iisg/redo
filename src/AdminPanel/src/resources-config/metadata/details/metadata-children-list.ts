import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class MetadataChildrenList {
  @bindable metadata: Metadata;
  @bindable(twoWay) metadataChildrenList: Metadata[];

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
