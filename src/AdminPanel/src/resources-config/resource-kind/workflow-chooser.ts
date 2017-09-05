import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class WorkflowChooser implements ComponentAttached {
  @bindable(twoWay)
  value: Workflow;

  workflows: Workflow[];

  constructor(private workflowRepository: WorkflowRepository) {
  }

  attached() {
    this.workflowRepository.getList().then((workflows) => {
      this.workflows = workflows;
    });
  }
}
