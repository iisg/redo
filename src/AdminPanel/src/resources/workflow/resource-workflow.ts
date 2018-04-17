import {Resource} from "../resource";
import {bindable} from "aurelia-templating";
import {Workflow} from "workflows/workflow";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceWorkflow {
  @bindable resource: Resource;
  workflow: Workflow;
  fetchingTransitions = false;

  resourceChanged() {
    this.workflow = this.resource.kind.workflow;
  }
}
