import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {bindable} from "aurelia-templating";
import {DeleteEntityConfirmation} from "../common/dialog/delete-entity-confirmation";
import {setPendingRequest, clearPendingRequest} from "../common/entity/entity";
import {Router} from "aurelia-router";
import {bindingMode, observable} from "aurelia-binding";

@autoinject
export class ResourcesList {
  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;

  addFormOpened: boolean = false;

  @observable resources: Resource[];

  constructor(private resourceRepository: ResourceRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private router: Router) {
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
    if (resource.pendingRequest) {
      return;
    }
    this.deleteEntityConfirmation.confirm('resource', resource.id)
      .then(setPendingRequest(resource))
      .then(() => this.resourceRepository.remove(resource))
      .then(() => this.removeDeletedResource(resource))
      .finally(clearPendingRequest(resource));
  }

  private removeDeletedResource(resource: Resource) {
    const index = this.resources.map(resource => resource.id).indexOf(resource.id);
    if (index != -1) {
      this.resources.splice(index, 1);
    }
  }

  navigate(resource: Resource) {
    this.router.navigateToRoute('resources/details', {id: resource.id});
  }
}
