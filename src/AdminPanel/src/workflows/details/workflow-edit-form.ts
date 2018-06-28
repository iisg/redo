import {autoinject} from "aurelia-dependency-injection";
import {DOM} from "aurelia-framework";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {booleanAttribute} from "common/components/boolean-attribute";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {Workflow} from "../workflow";
import {WorkflowGraphEditor} from "./graph/workflow-graph-editor";
import {WorkflowGraphEditorReady} from "./graph/workflow-graph-events";
import {WorkflowGraphManager} from "./graph/workflow-graph-manager";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {EntitySerializer} from "../../common/dto/entity-serializer";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";
import {ComponentAttached} from "aurelia-templating";

@autoinject
export class WorkflowEditForm extends ChangeLossPreventerForm implements ComponentAttached {
  @bindable edit: Workflow;
  @bindable viewing: boolean;
  @bindable onCancel = () => {
    this.router.navigateToRoute('workflows', {resourceClass: this.workflow.resourceClass});
  };
  @bindable @booleanAttribute editing: boolean;
  private controller: ValidationController;
  private editor: WorkflowGraphEditor;
  workflow: Workflow;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private router: Router,
              private element: Element,
              private graphManager: WorkflowGraphManager,
              private entitySerializer: EntitySerializer,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  attached() {
    this.changeLossPreventer.enable(this);
  }

  editChanged() {
    this.workflow = this.edit ? this.entitySerializer.clone(this.edit) : new Workflow();
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
