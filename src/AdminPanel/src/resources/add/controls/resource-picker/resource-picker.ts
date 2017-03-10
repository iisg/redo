import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {ResourceRepository} from "../../../resource-repository";
import {Resource} from "../../../resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class ResourcePicker implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Resource;
  @bindable({defaultBindingMode: bindingMode.twoWay}) resourceId: number;
  @bindable resourceKindFilter: ResourceKind[] = [];

  allResources: Resource[];
  resources: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
  }

  attached() {
    this.resourceRepository.getList().then((resources) => {
      this.allResources = resources;
      this.updateFilteredResources();
    });
  }

  private updateFilteredResources() {
    if (!this.allResources) {
      return;
    }
    if (this.resourceKindFilter.length == 0) {
      this.resources = this.allResources;
    } else {
      this.resources = this.filterResourcesByKinds(this.resourceKindFilter);
    }
  }

  private filterResourcesByKinds(resourceKindFilter: ResourceKind[]) {
    const allowedResourceKindIds: number[] = resourceKindFilter.map(
      resourceKind => (resourceKind.hasOwnProperty('id') ? resourceKind.id : resourceKind) as number
    );
    return this.allResources.filter(
      resource => allowedResourceKindIds.indexOf(resource.kind.id) != -1
    );
  }

  valueChanged(newValue: Resource) {
    if (newValue.id != this.resourceId) {
      this.resourceId = newValue.id;
    }
  }

  resourceIdChanged(newValueId: number) {
    if (!this.value || newValueId != this.value.id) {
      this.resourceRepository.get(newValueId).then((resource) => {
        this.value = resource;
      });
    }
  }

  resourceKindFilterChanged() {
    this.updateFilteredResources();
  }
}
