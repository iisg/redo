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
    const parents = newResource.contents[SystemMetadata.PARENT.id];
    this.parentId = parents.length ? parents[0].value : undefined;
  }
}
