import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";

export class ResourceLevelUp {
  @bindable resource: Resource;

  private parentId: number;
  private routerParams = {id: undefined};

  resourceChanged(newResource: Resource) {
    if (newResource == undefined) {
      this.parentId = undefined;
      return;
    }
    this.parentId = (newResource.contents[SystemMetadata.PARENT.baseId] || [])[0];
    this.routerParams.id = this.parentId;
  }
}
