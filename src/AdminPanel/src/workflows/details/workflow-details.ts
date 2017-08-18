import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";
import {autoinject} from "aurelia-dependency-injection";
import {RoutableComponentActivate, RouteConfig, NavigationInstruction, Router, NavModel} from "aurelia-router";
import {I18N} from "aurelia-i18n";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {Subscription, EventAggregator} from "aurelia-event-aggregator";
import {deepCopy} from "common/utils/object-utils";
import {BindingSignaler} from "aurelia-templating-resources";
import {WorkflowGraphEditor} from "./graph/workflow-graph-editor";

@autoinject
export class WorkflowDetails implements RoutableComponentActivate {
  readonly UPDATE_SIGNAL = 'workflow-details-updated';

  workflow: Workflow;
  originalWorkflow: Workflow;
  editing: boolean = false;

  private urlListener: Subscription;
  private navModel: NavModel;

  constructor(private workflowRepository: WorkflowRepository,
              private ea: EventAggregator,
              private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private router: Router,
              private signaler: BindingSignaler) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success", (event: {instruction: NavigationInstruction}) => {
      this.editing = (event.instruction.queryParams.action == 'edit');
      if (this.workflow != undefined) {
        if (this.editing) {  // read-only -> editing
          this.originalWorkflow = deepCopy(this.workflow);
        } else {  // editing -> read-only
          this.workflow.copyFrom(this.originalWorkflow);
          this.updateWindowTitle();
        }
      }
    });
  }

  unbind() {
    this.urlListener.dispose();
  }

  activate(params: any, routeConfig: RouteConfig): void {
    this.workflowRepository.get(params.id).then(workflow => {
      this.workflow = workflow;
      if (this.editing) {
        this.originalWorkflow = deepCopy(this.workflow);
      }
      this.navModel = routeConfig.navModel;
      this.updateWindowTitle();
    });
  }

  private updateWindowTitle() {
    this.navModel.setTitle(this.inCurrentLanguage.toView(this.workflow.name) + ' - ' + this.i18n.tr('Workflows'));
  }

  toggleEditForm() {
    // link can't be generated in the view with route-href because it is impossible to set replace:true there
    // see https://github.com/aurelia/templating-router/issues/54
    this.router.navigateToRoute('workflows/details', {id: this.workflow.id, action: this.editing ? undefined : 'edit'}, {replace: true});
  }

  update(): Promise<any> {
    this.workflow.pendingRequest = true;
    this.workflow[WorkflowGraphEditor.UPDATE_FROM_GRAPH]();
    return this.workflowRepository
      .update(this.workflow)
      .then(() => {
        this.originalWorkflow = deepCopy(this.workflow);
        this.originalWorkflow.pendingRequest = false;
        this.toggleEditForm();
        this.signaler.signal(this.UPDATE_SIGNAL);
      })
      .finally(() => this.workflow.pendingRequest = false);
  }
}
