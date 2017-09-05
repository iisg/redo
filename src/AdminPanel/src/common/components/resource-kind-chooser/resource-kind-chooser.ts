import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {twoWay, oneTime} from "../binding-mode";

@autoinject
export class ResourceKindChooser implements ComponentAttached {
  @bindable(twoWay) value: ResourceKind|ResourceKind[];
  @bindable(oneTime) multiSelect: boolean = false;
  @bindable disabled: boolean = false;
  @bindable systemResourceKinds: boolean = false;

  resourceKinds: ResourceKind[] = [];

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
