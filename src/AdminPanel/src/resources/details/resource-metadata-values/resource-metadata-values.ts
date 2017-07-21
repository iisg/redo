import {bindable} from "aurelia-templating";
import {Resource} from "../../resource";
import {Metadata} from "../../../resources-config/metadata/metadata";
import {computedFrom} from "aurelia-binding";

export class ResourceMetadataValues {
  @bindable resource: Resource;
  @bindable brief: boolean;

  briefChanged() {
    if (this.brief as any === '') { // when used without value: <resource-metadata-values brief>
      this.brief = true;
    }
  }

  @computedFrom('resource', 'brief')
  get metadataList(): Metadata[] {
    if (this.resource.kind == undefined) {
      return [];
    }
    return this.brief && (this.resource.kind.briefMetadataList.length > 0)
      ? this.resource.kind.briefMetadataList
      : this.resource.kind.metadataList;
  }
}
