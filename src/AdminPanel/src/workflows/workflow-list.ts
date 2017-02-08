import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {WorkflowRepository} from "./workflow-repository";

@autoinject
export class WorkflowList implements ComponentAttached {
  addFormOpened: boolean = false;

  workflows: Array<Workflow>;

  constructor(private workflowRepository: WorkflowRepository) {
  }

  attached(): void {
    this.workflowRepository.getList().then(workflows => this.workflows = workflows);
  }
}
