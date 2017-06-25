import {bindable} from "aurelia-templating";
import {Resource} from "../../resource";
import {Metadata} from "../../../resources-config/metadata/metadata";
import {computedFrom} from "aurelia-binding";

export class ResourceMetadataValues {
  @bindable resource: Resource;
  @bindable brief: boolean;

  @computedFrom('resource', 'brief')
  get metadataList(): Metadata[] {
    return (this.brief != undefined) && (this.resource.kind.briefMetadataList.length > 0)
      ? this.resource.kind.briefMetadataList
      : this.resource.kind.metadataList;
  }
}
