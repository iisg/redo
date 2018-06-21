import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {computedFrom} from "aurelia-binding";

export class NewMetadataValueButton {
  @bindable metadata: Metadata;
  @bindable resource: Resource;

  @computedFrom('metadata.constraints.maxCount', 'values.length')
  get canAddMore() {
    return !this.metadata.constraints.maxCount || this.values.length < this.metadata.constraints.maxCount;
  }

  @computedFrom('resource.contents', 'metadata.id')
  get values() {
    return this.resource.contents[this.metadata.id];
  }
}
