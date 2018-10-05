import {bindable} from "aurelia-templating";
import {Resource} from "../../../resources/resource";
import {ResourceRepository} from "../../../resources/resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "../../../resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "../../../resources-config/resource-kind/resource-kind-repository";
import {computedFrom} from "aurelia-binding";
import {WorkflowTransition} from "../../../workflows/workflow";

@autoinject
export class DepositForm {
  @bindable parentId: number;
  @bindable resourceClass: string;
  @bindable resourceKindId: number;
  @bindable editedResourceId: number;
  @bindable resourceUrl = '/resources/%s';
  @bindable depositUrl = '/deposit/%s';
  @bindable transitionId: string;

  parentResource: Resource;
  resourceKind: ResourceKind;
  editedResource: Resource;
  transition: WorkflowTransition;

  constructor(private resourceRepository: ResourceRepository, private resourceKindRepository: ResourceKindRepository) {
  }

  async bind() {
    this.parentResource = this.parentId ? await this.resourceRepository.get(this.parentId) : undefined;
    if (this.editedResourceId) {
      this.resourceRepository.get(this.editedResourceId).then(resource => {
        this.editedResource = resource;
        this.resourceKind = resource.kind;
        if (this.resourceKind.workflow && this.transitionId) {
          this.transition = this.editedResource.availableTransitions.filter(item => item.id === this.transitionId)[0];
        }
      });
    } else {
      this.resourceKind = await this.resourceKindRepository.get(this.resourceKindId);
      this.editedResource = new Resource();
      this.editedResource.kind = this.resourceKind;
      this.editedResource.resourceClass = this.resourceClass;
    }
  }

  navigateToResourceUrl(resourceId: number) {
    let targetUrl = this.resourceUrl.replace("%s", '' + resourceId);
    window.location.assign(targetUrl);
  }

  navigateToDepositUrl(path: string) {
    let targetUrl = this.depositUrl.replace("%s", path);
    window.location.assign(targetUrl);
  }

  get relationshipUrl(): string {
    return this.depositUrl.replace("%s", 'tree');
  }

  saveResource(resource: Resource, transitionId: string): Promise<any> {
    resource.resourceClass = this.resourceClass;
    if (!this.editedResourceId) {
      return this.resourceRepository.post(resource).then(resource => {
        this.redirectResource(resource);
      });
    } else {
      return this.resourceRepository.updateAndApplyTransition(resource, transitionId).then(resource => {
        this.redirectResource(resource);
      });
    }
  }

  private redirectResource(resource: Resource) {
    const availableTransitions = resource.availableTransitions.filter(transition => {
      return transition.id !== 'update';
    });
    if (availableTransitions.length && resource.canApplyTransition(availableTransitions[0])) {
      const url = `form?parentResourceId=${this.parentId}&resourceKindId=${this.resourceKindId}`
        + `&edit=${resource.id}&transitionId=${availableTransitions[0].id}`;
      this.navigateToDepositUrl(url);
    } else {
      this.navigateToResourceUrl(resource.id);
    }
  }

  backToList() {
    const path = this.editedResourceId ? 'deposit-list' : 'resource-kind';
    this.navigateToDepositUrl(path);
  }

  @computedFrom('parentResource', 'editedResourceId', 'resourceKind')
  get showForm(): boolean {
    const isParentResource = this.parentId
      ? !!this.parentResource
      : true;
    if (this.editedResourceId) {
      return !!this.editedResource && isParentResource;
    } else {
      return !!this.resourceKind && isParentResource;
    }
  }
}
