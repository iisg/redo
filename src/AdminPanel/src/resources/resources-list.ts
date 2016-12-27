import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";

@autoinject
export class ResourcesList {
  addFormOpened: boolean = false;

  resources: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
    this.resourceRepository.getList()
      .then(resources => this.resources = resources)
      .then(() => this.addFormOpened || (this.addFormOpened = this.resources.length == 0));
  }

  addNewResource(resource: Resource): Promise<Resource> {
    return this.resourceRepository.post(resource).then(resource => {
      this.addFormOpened = false;
      this.resources.push(resource);
      return resource;
    });
  }
}
