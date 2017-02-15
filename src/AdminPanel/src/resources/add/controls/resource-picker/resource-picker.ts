import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {ResourceRepository} from "../../../resource-repository";
import {Resource} from "../../../resource";

@autoinject
export class ResourcePicker implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: Resource;
  @bindable({defaultBindingMode: bindingMode.twoWay})
  resourceId: number;

  resources: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
  }

  attached() {
    this.resourceRepository.getList().then((resources) => {
      this.resources = resources;
    });
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
}
