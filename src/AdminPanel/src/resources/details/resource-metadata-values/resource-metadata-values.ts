import {bindable} from "aurelia-templating";
import {Resource} from "../../resource";
import {Metadata} from "resources-config/metadata/metadata";

export class ResourceMetadataValues {
  @bindable resource: Resource;
  @bindable metadata: Metadata;

  determinesAssignee(resource: Resource, metadata: Metadata): boolean {
    return resource.transitionAssigneeMetadata !== undefined
      && metadata != undefined
      && resource.transitionAssigneeMetadata.hasOwnProperty(metadata.baseId);
  }
}
