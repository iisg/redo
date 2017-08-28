import {ResourceKindRepository} from "./resource-kind-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";
import {Alert} from "common/dialog/alert";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";

@autoinject
export class ResourceKindList {
  addFormOpened: boolean = false;

  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private alert: Alert,
              private deleteEntityConfirmation: DeleteEntityConfirmation) {
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
    resourceKind.pendingRequest = true;
    return resourceKind.getWorkflow()
      .then(workflow => (resourceKind.pendingRequest = false) || workflow)
      .then(workflow => {
        const title = this.inCurrentLanguage.toView(workflow.name);
        return this.alert.show({imageUrl: workflow.thumbnail}, title);
      });
  }

  saveEditedResourceKind(resourceKind: ResourceKind, changedResourceKind: ResourceKind): Promise<any> {
    resourceKind.pendingRequest = true;
    return this.resourceKindRepository.update(changedResourceKind)
      .then(updated => $.extend(resourceKind, updated))
      .finally(() => resourceKind.pendingRequest = false);
  }

  deleteResourceKind(resourceKind: ResourceKind): Promise<any> {
    return this.deleteEntityConfirmation.confirm('resourceKind', resourceKind.id)
      .then(() => resourceKind.pendingRequest = true)
      .then(() => this.resourceKindRepository.remove(resourceKind))
      .then(() => {
        const index = this.resourceKinds.findIndex(rk => rk.id == resourceKind.id);
        this.resourceKinds.splice(index, 1);
      })
      .finally(() => resourceKind.pendingRequest = false);
  }
}
