import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {bindable} from "aurelia-templating";

@autoinject
export class ResourcesList {
  @bindable parentResource: Resource = undefined;

  addFormOpened: boolean = false;

  resources: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
  }

  attached(): void {
    this.resourceRepository.getByParent(this.parentResource).then(resources => {
      this.resources = resources;
      if (!this.addFormOpened) {
        this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined);
      }
    });
  }

  addNewResource(resource: Resource): Promise<Resource> {
    return this.resourceRepository.post(resource).then(resource => {
      this.addFormOpened = false;
      this.resources.push(resource);
      return resource;
    });
  }
}
