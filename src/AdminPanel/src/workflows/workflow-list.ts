import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {WorkflowRepository} from "./workflow-repository";
import {removeByValue} from "../common/utils/array-utils";
import {DeleteEntityConfirmation} from "../common/dialog/delete-entity-confirmation";

@autoinject
export class WorkflowList implements ComponentAttached {
  addFormOpened: boolean = false;

  workflows: Array<Workflow>;

  constructor(private workflowRepository: WorkflowRepository, private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  async attached() {
    this.workflows = await this.workflowRepository.getList();
  }

  deleteWorkflow(workflow: Workflow) {
    this.deleteEntityConfirmation.confirm('workflow', workflow.name)
      .then(() => workflow.pendingRequest = true)
      .then(() => this.workflowRepository.remove(workflow))
      .then(() => removeByValue(this.workflows, workflow))
      .finally(() => workflow.pendingRequest = false);
  }
}
