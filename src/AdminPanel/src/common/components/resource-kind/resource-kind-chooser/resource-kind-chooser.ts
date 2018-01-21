import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {oneTime, twoWay} from "../../binding-mode";

@autoinject
export class ResourceKindChooser implements ComponentAttached {
  @bindable(twoWay) value: ResourceKind|ResourceKind[];
  @bindable(oneTime) multiSelect: boolean = false;
  @bindable disabled: boolean = false;
  @bindable resourceClass: string;
  @bindable filter: (resourceKind: ResourceKind) => boolean = () => true;

  resourceKinds: ResourceKind[] = [];

  constructor(private resourceKindRepository: ResourceKindRepository) {
  }

  attached() {
    const promise = this.resourceClass
      ? this.resourceKindRepository.getListByClass(this.resourceClass)
      : this.resourceKindRepository.getList();
    promise.then(resourceKinds => {
      this.resourceKinds = resourceKinds.filter(this.filter);
    });
  }
}
