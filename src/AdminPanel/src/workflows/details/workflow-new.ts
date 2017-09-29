import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";
import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";

@autoinject
export class WorkflowNew {
  workflow: Workflow = new Workflow();

  constructor(private workflowRepository: WorkflowRepository,
              private router: Router) {
  }

  async addWorkflow(): Promise<any> {
    this.workflow.pendingRequest = true;
    const savedWorkflow = await this.workflowRepository.post(this.workflow);
    this.workflow.pendingRequest = false;
    this.router.navigateToRoute('workflows/details', savedWorkflow);
  }
}
