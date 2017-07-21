import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode, observable} from "aurelia-binding";
import {ResourceRepository} from "resources/resource-repository";
import {Resource} from "resources/resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class ResourcePicker implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) resourceId: number;
  @bindable resourceKindFilter: ResourceKind[] = [];
  @bindable disabled: boolean = false;

  @observable value: Resource;

  initialized: boolean = false;
  allResources: Resource[];
  resources: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
  }

  attached() {
    this.resourceRepository.getListWithSystemResourceKinds().then((resources) => {
      this.allResources = resources;
      this.updateFilteredResources();
      this.resourceIdChanged(this.resourceId);
    });
    this.initialized = true;
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
      resource => (resource.kind != undefined) && allowedResourceKindIds.indexOf(resource.kind.id) != -1
    );
  }

  valueChanged(newValue: Resource) {
    if (!this.initialized) {
      return;
    }
    if (newValue == undefined) {
      this.resourceId = undefined;
    } else if (newValue.id != this.resourceId) {
      this.resourceId = newValue.id;
    }
  }

  resourceIdChanged(newResourceId: number) {
    if (newResourceId == undefined) {
      this.value = undefined;
      return;
    }
    if (!this.value || newResourceId != this.value.id) {
      this.value = this.findResourceById(newResourceId);
    }
  }

  findResourceById(id: number): Resource {
    if (this.allResources == undefined) {
      return undefined;
    }
    for (let resource of this.allResources) {
      if (resource.id == id) {
        return resource;
      }
    }
    return undefined;
  }

  resourceKindFilterChanged() {
    this.updateFilteredResources();
  }
}
