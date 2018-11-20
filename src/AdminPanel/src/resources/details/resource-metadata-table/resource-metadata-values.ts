import {bindable} from "aurelia-templating";
import {Resource} from "resources/resource";
import {Metadata} from "resources-config/metadata/metadata";
import {computedFrom} from "aurelia-binding";

export class ResourceMetadataValues {
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable checkMetadataBrief: boolean = false;

  @computedFrom('resource', 'metadata')
  get determinesAssignee(): boolean {
    return this.metadata !== undefined
      && this.resource.transitionAssigneeMetadata !== undefined
      && this.resource.transitionAssigneeMetadata.hasOwnProperty(this.metadata.id);
  }
}
