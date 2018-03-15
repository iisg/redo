import {ResourceKindRepository} from "./resource-kind-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";
import {Alert} from "common/dialog/alert";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {ContextResourceClass} from 'resources/context/context-resource-class';

@autoinject
export class ResourceKindList {
  addFormOpened: boolean = false;
  progressBar: boolean;
  resourceClass: string;
  resourceKinds: ResourceKind[];

  constructor(private resourceKindRepository: ResourceKindRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private alert: Alert,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
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

  displayWorkflowPreview(resourceKind: ResourceKind): Promise<any> {
    const title = this.inCurrentLanguage.toView(resourceKind.workflow.name);
    return this.alert.show({imageUrl: resourceKind.workflow.thumbnail}, title);
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
