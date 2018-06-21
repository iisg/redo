import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, NavModel, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {BindingSignaler} from "aurelia-templating-resources";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {ContextResourceClass} from 'resources/context/context-resource-class';
import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";

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
              private signaler: BindingSignaler,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass) {
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success", (event: {instruction: NavigationInstruction}) => {
      this.editing = (event.instruction.queryParams.action == 'edit');
      if (this.workflow) {
        if (this.editing) {  // read-only -> editing
          this.originalWorkflow = this.entitySerializer.clone(this.workflow);
        } else if (this.originalWorkflow) {  // editing -> read-only or loading
          this.entitySerializer.hydrateClone(this.originalWorkflow, this.workflow);
          this.updateWindowTitle();
        }
      }
    });
  }

  unbind() {
    this.urlListener.dispose();
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.workflow = await this.workflowRepository.get(params.id);
    this.contextResourceClass.setCurrent(this.workflow.resourceClass);
    this.navModel = routeConfig.navModel;
    this.updateWindowTitle();

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
    return this.workflowRepository
      .update(this.workflow)
      .then(() => {
        this.originalWorkflow = undefined;
        this.toggleEditForm();
        this.signaler.signal(this.UPDATE_SIGNAL);
      })
      .finally(() => this.workflow.pendingRequest = false);
  }

  deleteWorkflow(): Promise<any> {
    return this.deleteEntityConfirmation.confirm('workflow', this.workflow.id)
      .then(() => this.workflow.pendingRequest = true)
      .then(() => this.workflowRepository.remove(this.workflow))
      .then(() => this.router.navigateToRoute('workflows', {resourceClass: this.workflow.resourceClass}))
      .finally(() => this.workflow.pendingRequest = false);
  }
}
