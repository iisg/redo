import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {NavModel, RouteConfig, Router} from "aurelia-router";
import {BindingSignaler} from "aurelia-templating-resources";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {cloneDeep} from "lodash";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";
import {WorkflowGraphEditor} from "./graph/workflow-graph-editor";
import {WorkflowGraphEditorReady} from "./graph/workflow-graph-events";
import {WorkflowGraphManager} from "./graph/workflow-graph-manager";

@autoinject
export class WorkflowForm extends ChangeLossPreventerForm {
  readonly UPDATE_SIGNAL = 'workflow-updated';

  viewing: boolean;
  workflow: Workflow;
  editing: boolean;
  resourceKinds: ResourceKind[];
  dirty: boolean;
  private originalWorkflow: Workflow;
  private editor: WorkflowGraphEditor;
  private controller: ValidationController;
  private navigationModel: NavModel;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private router: Router,
              private contextResourceClass: ContextResourceClass,
              private workflowRepository: WorkflowRepository,
              private resourceKindRepository: ResourceKindRepository,
              private graphManager: WorkflowGraphManager,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private i18n: I18N,
              private signaler: BindingSignaler,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer());
  }

  async activate(params: any, routeConfig: RouteConfig) {
    if (params.id) {
      this.viewing = true;
      this.editing = true;
      this.resourceKindRepository.getListQuery()
        .filterByWorkflowId(params.id)
        .get()
        .then(resourceKinds => this.resourceKinds = resourceKinds);
      let workflow = await this.workflowRepository.get(params.id);
      this.workflow = workflow;
      this.contextResourceClass.setCurrent(this.workflow.resourceClass);
      this.navigationModel = routeConfig.navModel;
      this.updateWindowTitle();
      this.originalWorkflow = cloneDeep(this.workflow);
    } else {
      this.changeLossPreventer.enable(this);
      this.workflow = new Workflow();
      this.workflow.resourceClass = params.resourceClass;
    }
  }

  private updateWindowTitle() {
    this.navigationModel.setTitle(this.inCurrentLanguage.toView(this.workflow.name) + ' - ' + this.i18n.tr('Workflows'));
  }

  updateGraphPosition(): void {
    this.graphManager.forEach(graph => graph.recalculateGraphPosition());
  }

  onEditorReady(event: WorkflowGraphEditorReady) {
    this.editor = event.detail.editor;
  }

  toggleEditForm() {
    if (this.viewing) {
      this.changeLossPreventer.enable(this);
    } else {
      this.changeLossPreventer.disable();
    }
    this.viewing = !this.viewing;
  }

  submit() {
    this.editor.updateWorkflowBasedOnGraph(true);
    this.controller.validate().then(result => {
      if (result.valid) {
        if (this.editing) {
          this.update();
        } else {
          this.addWorkflow();
        }
      }
    });
  }

  private update(): Promise<any> {
    this.workflow.pendingRequest = true;
    return this.workflowRepository
      .update(this.workflow)
      .then(() => {
        this.viewing = !this.viewing;
        this.signaler.signal(this.UPDATE_SIGNAL);
        this.originalWorkflow = cloneDeep(this.workflow);
        this.originalWorkflow.pendingRequest = false;
      })
      .finally(() => this.workflow.pendingRequest = false);
  }

  private async addWorkflow() {
    this.workflow.pendingRequest = true;
    try {
      const savedWorkflow = await this.workflowRepository.post(this.workflow);
      this.changeLossPreventer.disable();
      this.router.navigateToRoute('workflows/details', {id: savedWorkflow.id});
    } finally {
      this.workflow.pendingRequest = false;
    }
  }

  cloneWorkflow() {
    this.editor.updateWorkflowBasedOnGraph(true);
    this.controller.validate().then(result => {
      if (result.valid) {
        this.addWorkflow();
      }
    });
  }

  cancel() {
    this.changeLossPreventer.canLeaveView().then(canLeaveView => {
      if (canLeaveView) {
        if (this.editing) {
          if (this.dirty) {
            this.workflow = cloneDeep(this.originalWorkflow);
          }
          this.viewing = !this.viewing;
        } else {
          this.router.navigateToRoute('workflows', {resourceClass: this.workflow.resourceClass});
        }
      }
    });
  }

  deleteWorkflow(): Promise<any> {
    return this.deleteEntityConfirmation.confirm('workflow', this.workflow.id)
      .then(() => this.workflow.pendingRequest = true)
      .then(() => this.workflowRepository.remove(this.workflow))
      .then(() => this.router.navigateToRoute('workflows', {resourceClass: this.workflow.resourceClass}))
      .finally(() => this.workflow.pendingRequest = false);
  }
}
