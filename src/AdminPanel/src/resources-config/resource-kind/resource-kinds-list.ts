import {ResourceKindRepository} from "./resource-kind-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {ContextResourceClass} from "resources/context/context-resource-class";

@autoinject
export class ResourceKindsList {
  addFormOpened: boolean = false;
  progressBar: boolean;
  resourceClass: string;
  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository,
              private contextResourceClass: ContextResourceClass) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
    if (this.resourceKinds) {
      this.resourceKinds = [];
    }
    this.progressBar = true;
    this.getResourceKinds();
  }

  getResourceKinds() {
    this.resourceKindRepository.getListByClass(this.resourceClass)
      .then(resourceKinds => {
        this.progressBar = false;
        this.resourceKinds = resourceKinds;
        this.addFormOpened = this.resourceKinds.length == 0;
      });
  }

  addNewResourceKind(resourceKind: ResourceKind): Promise<ResourceKind> {
    resourceKind.resourceClass = this.resourceClass;
    return this.resourceKindRepository.post(resourceKind).then(resourceKind => {
      this.addFormOpened = false;
      this.resourceKinds.push(resourceKind);
      return resourceKind;
    });
  }

  toggleEditForm() {
    this.addFormOpened = !this.addFormOpened;
  }
}
