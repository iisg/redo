import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "./workflow";
import {WorkflowRepository} from "./workflow-repository";
import {bindable} from "aurelia-templating";
import {ContextResourceClass} from 'resources/context/context-resource-class';

@autoinject
export class WorkflowList {
  @bindable resourceClass: string;

  addFormOpened: boolean = false;
  progressBar: boolean;

  workflows: Array<Workflow>;

  constructor(private workflowRepository: WorkflowRepository,
              private contextResourceClass: ContextResourceClass) {
  }

  activate(params: any) {
    this.resourceClass = params.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
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
}
