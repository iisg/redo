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
import {Resource} from "../../resources/resource";
import {WorkflowRepository} from "../workflow-repository";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";

@autoinject
export class WorkflowForm extends ChangeLossPreventerForm {
  @bindable workflow: Workflow = new Workflow();
  @bindable viewing: boolean;
  @bindable submit: (value: { savedResource: Resource, transitionId: string }) => Promise<any>;
  @bindable onCancel: VoidFunction = () => undefined;
  @bindable @booleanAttribute editing: boolean;
  private controller: ValidationController;
  private editor: WorkflowGraphEditor;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private router: Router,
              private element: Element,
              private workflowRepository: WorkflowRepository,
              private graphManager: WorkflowGraphManager,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  viewingChanged() {
    if (!this.viewing) {
      this.changeLossPreventer.enable(this);
    }
  }

  activate(params: any) {
    this.workflow.resourceClass = params.resourceClass;
  }

  updateGraphPosition(): void {
    this.graphManager.forEach(graph => graph.recalculateGraphPosition());
  }

  onEditorReady(event: WorkflowGraphEditorReady) {
    this.editor = event.detail.editor;
  }

  onSubmit() {
    if (this.editing) {
      this.saveWorkflow();
    } else {
      this.addWorkflow();
    }
  }

  async saveWorkflow(): Promise<any> {
    this.workflow.pendingRequest = true;
    this.editor.updateWorkflowBasedOnGraph(true);
    this.controller.validate().then(result => {
      if (result.valid) {
        this.changeLossPreventer.disable();
        this.element.dispatchEvent(SubmitEvent.newInstance());
      }
    });
    this.router.navigateToRoute(
      'workflows/details',
      {id: this.workflow.id, action: this.editing ? undefined : 'edit'}, {replace: true}
    );
    this.workflow.pendingRequest = false;
  }

  async addWorkflow(): Promise<any> {
    this.workflow.pendingRequest = true;
    try {
      this.editor.updateWorkflowBasedOnGraph(true);
      const savedWorkflow = await this.workflowRepository.post(this.workflow);
      this.changeLossPreventer.disable();
      this.router.navigateToRoute('workflows/details', {id: savedWorkflow.id});
    } finally {
      this.workflow.pendingRequest = false;
    }
  }
}

class SubmitEvent {
  bubbles = true;

  static newInstance(): Event {
    return DOM.createCustomEvent('submit', new SubmitEvent());
  }
}
