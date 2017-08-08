import {ResourceKindRepository} from "./resource-kind-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";
import swal from "sweetalert2";

@autoinject
export class ResourceKindList {
  addFormOpened: boolean = false;

  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository, private inCurrentLanguage: InCurrentLanguageValueConverter) {
    resourceKindRepository.getList()
      .then(resourceKinds => this.resourceKinds = resourceKinds)
      .then(() => this.addFormOpened || (this.addFormOpened = this.resourceKinds.length == 0));
  }

  addNewResourceKind(resourceKind: ResourceKind): Promise<ResourceKind> {
    return this.resourceKindRepository.post(resourceKind).then(resourceKind => {
      this.addFormOpened = false;
      this.resourceKinds.push(resourceKind);
      return resourceKind;
    });
  }

  displayWorkflowPreview(resourceKind: ResourceKind): Promise<any> {
    return resourceKind.getWorkflow().then(workflow => {
      return swal({
        title: this.inCurrentLanguage.toView(workflow.name),
        imageUrl: workflow.thumbnail
      });
    });
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<ResourceKind> {
    return this.resourceKindRepository.update(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
      .then(() => (resourceKind['editing'] = false) || resourceKind);
  }
}
