import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {bindable} from "aurelia-templating";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {bindingMode, observable} from "aurelia-binding";
import {removeValue} from "common/utils/array-utils";
import {ComponentAttached} from "aurelia-templating";

@autoinject
export class ResourcesList implements ComponentAttached {
  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;

  @bindable resourceClass: string;

  addFormOpened: boolean;

  progressBar: boolean;

  @observable resources: Resource[];

  constructor(private resourceRepository: ResourceRepository,
              private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    if (this.resources) {
      this.resources = [];
    }
    this.addFormOpened = false;
    this.fetchResources();
  }

  attached() {
    if (this.parentResource) {
      this.fetchResourcesByParent();
    }
  }

  fetchResources() {
    this.progressBar = true;
    this.resourceRepository.getListByClass(this.resourceClass).then(resources => {
      this.progressBar = false;
      this.resources = resources;
      this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined);
    });
  }

  fetchResourcesByParent() {
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
    resource.resourceClass = this.resourceClass;
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
      .then(() => removeValue(this.resources, resource))
      .finally(() => resource.pendingRequest = false);
  }
}
