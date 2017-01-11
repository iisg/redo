import {ResourceKindRepository} from "./resource-kind-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";

@autoinject
export class ResourceKindList {
  addFormOpened: boolean = false;

  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository) {
    resourceKindRepository.getList()
      .then(resourceKinds => this.resourceKinds = resourceKinds)
      .then(() => this.addFormOpened || (this.addFormOpened = this.resourceKinds.length == 0));
  }

  addNewResourceKind(resourceKind: ResourceKind): Promise<ResourceKind> {
    return this.resourceKindRepository.post(resourceKind).then(resourceKind => {
      this.addFormOpened = false;
      this.resourceKinds.push(resourceKind);
      return resourceKind;
    });
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<ResourceKind> {
    return this.resourceKindRepository.update(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
      .then(() => (resourceKind['editing'] = false) || resourceKind);
  }
}
