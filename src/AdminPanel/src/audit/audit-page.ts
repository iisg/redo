import {Audit} from "./audit";

export class WorkflowsListPage {
  audit: Audit;
  private parameters: any;

  activate(parameters: any) {
    this.parameters = parameters;
    if (this.audit) {
      this.bind();
    }
  }

  bind() {
    this.audit.activate(this.parameters);
  }
}
