import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {bindable} from "aurelia-templating";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {bindingMode, observable} from "aurelia-binding";
import {removeByValue} from "../common/utils/array-utils";

@autoinject
export class ResourcesList {
  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;

  addFormOpened: boolean = false;

  @observable resources: Resource[];

  constructor(private resourceRepository: ResourceRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  attached(): void {
    this.resourceRepository.getByParent(this.parentResource).then(resources => {
      this.resources = resources;
      if (!this.addFormOpened) {
        this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined);
      }
    });
  }

  resourcesChanged(newResources: Resource[]) {
    this.hasResources = newResources.length > 0;
  }

  addNewResource(resource: Resource): Promise<Resource> {
    return this.resourceRepository.post(resource).then(resource => {
      this.addFormOpened = false;
      this.resources.push(resource);
      return resource;
    });
  }

  deleteResource(resource: Resource) {
    this.deleteEntityConfirmation.confirm('resource', resource.id)
      .then(() => resource.pendingRequest = true)
      .then(() => this.resourceRepository.remove(resource))
      .then(() => removeByValue(this.resources, resource))
      .finally(() => resource.pendingRequest = false);
  }
}
