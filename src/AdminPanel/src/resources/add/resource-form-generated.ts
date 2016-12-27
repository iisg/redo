import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {bindingMode} from "aurelia-binding";

export class ResourceFormGenerated {
  @bindable
  resourceKind: ResourceKind;

  @bindable({defaultBindingMode: bindingMode.twoWay})
  resource: Resource;

  resourceKindChanged() {
    this.resource.contents = {};
  }
}
