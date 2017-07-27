import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class WorkflowChooser implements ComponentAttached {
  @bindable(twoWay)
  value: Workflow;
  @bindable resourceClass: string;

  workflows: Workflow[];

  constructor(private workflowRepository: WorkflowRepository) {
  }

  attached() {
    this.workflowRepository.getListByClass(this.resourceClass).then((workflows) => {
      this.workflows = workflows;
    });
  }
}
