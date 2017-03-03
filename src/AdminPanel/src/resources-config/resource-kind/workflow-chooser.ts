import {ComponentAttached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {Workflow} from "../../workflows/workflow";
import {WorkflowRepository} from "../../workflows/workflow-repository";

@autoinject
export class WorkflowChooser implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
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
