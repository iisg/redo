import {bindable} from "aurelia-templating";
import {UpdateType} from "./update-type";
import {values} from 'lodash';
import {Metadata} from "../../resources-config/metadata/metadata";
import {computedFrom} from "aurelia-binding";
import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflows/workflow";
import {autoinject} from "aurelia-dependency-injection";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {ValidationController, ValidationControllerFactory, ValidationRules} from "aurelia-validation";
import {I18N} from "aurelia-i18n";
import {Router} from "aurelia-router";
import {ResourceListQuery} from "../resource-list-query";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {WorkflowRepository} from "../../workflows/workflow-repository";

@autoinject
export class BulkUpdateForm extends ChangeLossPreventerForm {
  @bindable totalCount: number;
  @bindable resourceClass: string;
  @bindable selectedUpdateType: UpdateType = UpdateType.APPEND;
  @bindable cancel: () => void;
  @bindable listQuery: ResourceListQuery;
  @bindable update: object;
  updateTypes: UpdateType[] = values(UpdateType);
  metadata: Metadata;
  displayStrategy: string;
  workflow: Workflow;
  placeOrTransition: WorkflowPlace | WorkflowTransition;
  addValuesAtBeginning: boolean;
  validationFailed = false;
  loaded = false;
  private validationController: ValidationController;
  private validationRenderer: BootstrapValidationRenderer;
  private rules: ValidationRules;

  constructor(private changeLossPreventer: ChangeLossPreventer,
              private i18n: I18N,
              private router: Router,
              private metadataRepository: MetadataRepository,
              private workflowRepository: WorkflowRepository,
              validationControllerFactory: ValidationControllerFactory) {
    super();
    this.validationController = validationControllerFactory.createForCurrentScope();
  }

  attached() {
    this.createRules();
    if (!this.validationRenderer) {
      this.validationRenderer = new BootstrapValidationRenderer();
      this.validationController.addRenderer(this.validationRenderer);
    }
    this.changeLossPreventer.enable(this);
    this.initializeUpdate().then(() => this.loaded = true);
  }

  initializeUpdate(): Promise<any> {
    if (this.update) {
      this.selectedUpdateType = this.update['action'];
      if (this.isContentsUpdateSelected) {
        this.displayStrategy = this.update['displayStrategy'];
        return this.metadataRepository.get(this.update['metadataId']).then(m => this.metadata = m);
      } else {
        return this.workflowRepository.get(this.update['workflow']).then(w => {
          this.workflow = w;
          this.placeOrTransition = this.update['action'] == UpdateType.MOVE_TO_PLACE
            ? w.places.find(p => p.id == this.update['placeId'])
            : w.transitions.find(t => t.id == this.update['transitionId']);
        });
      }
    }
    return Promise.resolve();
  }

  @computedFrom('selectedUpdateType')
  get isContentsUpdateSelected() {
    return this.selectedUpdateType == UpdateType.OVERRIDE || this.selectedUpdateType == UpdateType.APPEND;
  }

  @computedFrom('selectedUpdateType')
  get isWorkflowUpdateSelected() {
    return this.selectedUpdateType == UpdateType.MOVE_TO_PLACE || this.selectedUpdateType == UpdateType.EXECUTE_TRANSITION;
  }

  validateAndSubmit() {
    this.validationController.validate({object: this, rules: this.rules}).then(result => {
      if (result.valid) {
        this.changeLossPreventer.disable();
        this.validationFailed = false;
        this.router.navigateToRoute('resources/bulk-update-summary', this.queryParameters());
      } else {
        this.validationFailed = true;
      }
    });
  }

  createRules() {
    this.rules = ValidationRules.ensure('selectedUpdateType').required().withMessage('value_required')
      .ensure('metadata').required().when(() => this.isContentsUpdateSelected).withMessage('value_required')
      .ensure('displayStrategy').required().when(() => this.isContentsUpdateSelected).withMessage('value_required')
      .ensure('placeOrTransition').required().when(() => this.isWorkflowUpdateSelected).withMessage('value_required')
      .rules;
  }

  onChange() {
    this.dirty = true;
  }

  cancelForm() {
    this.changeLossPreventer.canLeaveView().then(canLeave => {
      if (canLeave) {
        this.cancel();
      }
    });
  }

  @computedFrom('selectedUpdateType')
  get entityChooserLabel() {
    const message = this.selectedUpdateType == UpdateType.MOVE_TO_PLACE
      ? 'Choose place to which you want to move resources'
      : 'Choose transition to execute';
    return this.i18n.tr(message);
  }

  queryParameters() {
    const action = {action: this.selectedUpdateType};
    const change: any = {};
    if (this.isContentsUpdateSelected) {
      change.metadataId = this.metadata.id;
      change.displayStrategy = this.displayStrategy;
      change.addValuesAtBeginning = this.addValuesAtBeginning;
    }
    if (this.isWorkflowUpdateSelected) {
      change.workflowId = this.workflow.id;
      $.extend(change, this.transitionChangeParams());
    }
    return {...action, ...change, ...this.listQueryToUrlParams(this.listQuery.getParams())};
  }

  private transitionChangeParams(): object {
    return this.selectedUpdateType == UpdateType.MOVE_TO_PLACE
      ? {placeId: this.placeOrTransition.id}
      : {transitionId: this.placeOrTransition.id};
  }

  listQueryToUrlParams(listQueryParams: any): object {
    const params = {resourceClass: this.resourceClass};
    if (this.workflow) {
      params['workflow'] = this.workflow.id;
    }
    if (listQueryParams['contentsFilter']) {
      params['contentsFilter'] = JSON.stringify(listQueryParams['contentsFilter']);
    }
    if (listQueryParams['resourceKinds']) {
      params['kindFilter'] = JSON.stringify(listQueryParams['resourceKinds']);
    }
    if (listQueryParams['workflowPlacesIds']) {
      params['placesFilter'] = JSON.stringify(listQueryParams['workflowPlacesIds']);
    }
    if (listQueryParams['parentId']) {
      params['parentId'] = listQueryParams['parentId'];
    } else if (!listQueryParams['topLevel']) {
      params['allLevels'] = true;
    }
    return params;
  }

  filterOutDynamicMetadata(metadata: Metadata): boolean {
    return !metadata.isDynamic;
  }
}
