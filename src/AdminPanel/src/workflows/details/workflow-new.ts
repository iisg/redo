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

  activate(params: any) {
    this.workflow.resourceClass = params.resourceClass;
  }

  async addWorkflow(): Promise<any> {
    this.workflow.pendingRequest = true;
    try {
      const savedWorkflow = await this.workflowRepository.post(this.workflow);
      this.router.navigateToRoute('workflows/details', {id: savedWorkflow.id});
    } finally {
      this.workflow.pendingRequest = false;
    }
  }
}
