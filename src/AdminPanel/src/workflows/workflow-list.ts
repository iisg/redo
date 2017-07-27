import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {WorkflowRepository} from "./workflow-repository";
import {removeValue} from "common/utils/array-utils";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {bindable} from "aurelia-templating";

@autoinject
export class WorkflowList {
  @bindable resourceClass: string;

  addFormOpened: boolean = false;
  progressBar: boolean;

  workflows: Array<Workflow>;

  constructor(private workflowRepository: WorkflowRepository, private deleteEntityConfirmation: DeleteEntityConfirmation) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    if (this.workflows) {
      this.workflows = [];
    }
    this.fetchWorkflows();
  }

  fetchWorkflows() {
    this.progressBar = true;
    this.workflowRepository.getListByClass(this.resourceClass).then(workflows => {
      this.progressBar = false;
      this.workflows = workflows;
    });
  }

  deleteWorkflow(workflow: Workflow) {
    this.deleteEntityConfirmation.confirm('workflow', workflow.name)
      .then(() => workflow.pendingRequest = true)
      .then(() => this.workflowRepository.remove(workflow))
      .then(() => removeValue(this.workflows, workflow))
      .finally(() => workflow.pendingRequest = false);
  }
}
