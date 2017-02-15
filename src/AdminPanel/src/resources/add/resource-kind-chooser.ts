import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class ResourceKindChooser implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: ResourceKind;

  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository) {
  }

  attached() {
    this.resourceKindRepository.getList().then((resourceKinds) => {
      this.resourceKinds = resourceKinds;
    });
  }
}
