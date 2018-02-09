import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {SystemMetadata} from "resources-config/metadata/system-metadata";

export class ResourceLevelUp {
  @bindable resource: Resource;

  private parentId: number;

  resourceChanged(newResource: Resource) {
    if (newResource == undefined) {
      this.parentId = undefined;
      return;
    }
    this.parentId = (newResource.contents[SystemMetadata.PARENT.id] || [])[0];
  }
}
