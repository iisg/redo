import {bindable} from "aurelia-templating";
import {Resource} from "../../resource";
import {Metadata} from "resources-config/metadata/metadata";
import {computedFrom} from "aurelia-binding";
import {booleanAttribute} from "common/components/boolean-attribute";

export class ResourceMetadataValues {
  @bindable resource: Resource;
  @bindable @booleanAttribute brief: boolean;

  @computedFrom('resource', 'brief')
  get metadataList(): Metadata[] {
    if (this.resource.kind == undefined) {
      return [];
    }
    return this.brief && (this.resource.kind.briefMetadataList.length > 0)
      ? this.resource.kind.briefMetadataList
      : this.resource.kind.metadataList;
  }

  determinesAssignee(resource: Resource, metadata: Metadata): boolean {
    return resource.transitionAssigneeMetadata !== undefined
      && resource.transitionAssigneeMetadata.hasOwnProperty(metadata.baseId);
  }
}
