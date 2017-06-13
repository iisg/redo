import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class ResourceKindChooser implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: ResourceKind|ResourceKind[];
  @bindable({defaultBindingMode: bindingMode.oneTime}) multiSelect: boolean = false;
  @bindable disabled: boolean = false;
  @bindable systemResourceKinds: boolean = false;

  private resourceKinds: ResourceKind[] = [];

  constructor(private resourceKindRepository: ResourceKindRepository) {
  }

  attached() {
    const promise = this.systemResourceKinds
      ? this.resourceKindRepository.getListWithSystemResourceKinds()
      : this.resourceKindRepository.getList();
    promise.then(resourceKinds => {
      this.resourceKinds = resourceKinds;
    });
  }
}
