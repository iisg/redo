import {Resource} from "../resource";
import {bindable} from "aurelia-templating";
import {Workflow} from "workflows/workflow";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "../resource-repository";

@autoinject
export class ResourceWorkflow {
  @bindable resource: Resource;

  workflow: Workflow;

  fetchingTransitions = false;

  constructor(private resourceRepository: ResourceRepository) {
  }

  applyTransition(transitionId: string) {
    this.fetchingTransitions = true;
    return this.resourceRepository.applyTransition(this.resource, transitionId).then(resource => {
      $.extend(this.resource, resource);
    }).finally(() => this.fetchingTransitions = false);
  }

  resourceChanged() {
    this.fetchingTransitions = true;
    this.resource.kind.getWorkflow().then(workflow => this.workflow = workflow).finally(() => this.fetchingTransitions = false);
  }
}
