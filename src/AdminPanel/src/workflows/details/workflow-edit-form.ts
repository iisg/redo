import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {bindable} from "aurelia-templating";
import {Workflow} from "../workflow";
import {autoinject} from "aurelia-dependency-injection";
import {DOM} from "aurelia-framework";
import {WorkflowGraphEditorReady} from "./graph/workflow-graph-events";
import {WorkflowGraphEditor} from "./graph/workflow-graph-editor";
import {WorkflowGraphManager} from "./graph/workflow-graph-manager";

@autoinject
export class WorkflowEditForm {
  @bindable workflow: Workflow = new Workflow;
  @bindable resourceClass: string;
  private controller: ValidationController;
  private editor: WorkflowGraphEditor;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private element: Element,
              private graphManager: WorkflowGraphManager) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  updateGraphPosition(): void {
    this.graphManager.forEach(graph => graph.recalculateGraphPosition());
  }

  onEditorReady(event: WorkflowGraphEditorReady) {
    this.editor = event.detail.editor;
  }

  onSubmit(event: Event) {
    event.stopPropagation();
    this.editor.updateWorkflowBasedOnGraph(true);
    this.controller.validate().then(result => {
      if (result.valid) {
        this.element.dispatchEvent(SubmitEvent.newInstance());
      }
    });
  }
}

class SubmitEvent {
  bubbles = true;

  static newInstance(): Event {
    return DOM.createCustomEvent('submit', new SubmitEvent());
  }
}
