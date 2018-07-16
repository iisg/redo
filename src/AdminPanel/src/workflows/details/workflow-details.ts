import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {NavModel, RoutableComponentActivate, RouteConfig, Router} from "aurelia-router";
import {BindingSignaler} from "aurelia-templating-resources";
import {DeleteEntityConfirmation} from "common/dialog/delete-entity-confirmation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";
import {cachedResponseRegistry} from "../../common/repository/cached-response";

@autoinject
export class WorkflowDetails implements RoutableComponentActivate {
  readonly UPDATE_SIGNAL = 'workflow-details-updated';

  workflow: Workflow;
  editing: boolean = false;

  private navModel: NavModel;

  constructor(private workflowRepository: WorkflowRepository,
              private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private router: Router,
              private signaler: BindingSignaler,
              private deleteEntityConfirmation: DeleteEntityConfirmation,
              private entitySerializer: EntitySerializer,
              private contextResourceClass: ContextResourceClass) {
  }

  async activate(params: any, routeConfig: RouteConfig) {
    this.workflow = undefined;
    this.workflow = await this.workflowRepository.get(params.id);
    this.contextResourceClass.setCurrent(this.workflow.resourceClass);
    this.navModel = routeConfig.navModel;
    this.updateWindowTitle();
    this.editing = params.action == 'edit';

  }

  private updateWindowTitle() {
    this.navModel.setTitle(this.inCurrentLanguage.toView(this.workflow.name) + ' - ' + this.i18n.tr('Workflows'));
  }

  toggleEditForm() {
    cachedResponseRegistry.clearAll(); // if we change sth in edition and then press 'cancel' in cache there is wrong graph
    this.router.navigateToRoute(
      'workflows/details',
      {id: this.workflow.id, action: this.editing ? undefined : 'edit'}, {replace: true}
    );
  }

  update(): Promise<any> {
    this.workflow.pendingRequest = true;
    return this.workflowRepository
      .update(this.workflow)
      .then(() => {
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
