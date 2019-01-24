import {WorkflowsList} from "./workflows-list";

export class WorkflowsListPage {
  workflowsList: WorkflowsList;
  private parameters: any;

  activate(parameters: any) {
    this.parameters = parameters;
    if (this.workflowsList) {
      this.bind();
    }
  }

  bind() {
    this.workflowsList.activate(this.parameters);
  }
}
