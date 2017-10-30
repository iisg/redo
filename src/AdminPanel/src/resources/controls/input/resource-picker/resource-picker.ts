import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {observable} from "aurelia-binding";
import {ResourceRepository} from "resources/resource-repository";
import {Resource} from "resources/resource";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourcePicker implements ComponentAttached {
  @bindable(twoWay) resourceId: number;
  @bindable resourceKindIds: number[] = [];
  @bindable disabled: boolean = false;

  @observable value: Resource;

  initialized: boolean = false;
  resources: Resource[];
  invalidValue: boolean = false;

  constructor(private resourceRepository: ResourceRepository) {
  }

  attached() {
    const query = this.resourceRepository.getListQuery();
    if (this.resourceKindIds.length > 0) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    query.get().then(resources => {
      this.resources = resources;
      this.resourceIdChanged(this.resourceId);
    });
    this.initialized = true;
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
    this.invalidValue = false;
    if (newResourceId == undefined) {
      this.value = undefined;
      return;
    }
    if (!this.value || newResourceId != this.value.id) {
      const value = this.findResourceById(newResourceId);
      if (value == undefined) {
        this.invalidValue = (this.resources != undefined);
      }
      else {
        this.value = value;
      }
    }
  }

  findResourceById(id: number): Resource {
    if (this.resources == undefined) {
      return undefined;
    }
    for (let resource of this.resources) {
      if (resource.id == id) {
        return resource;
      }
    }
    return undefined;
  }
}
