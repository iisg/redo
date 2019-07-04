import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {autoinject} from "aurelia-dependency-injection";
import {pick} from 'lodash';
import {UpdateType} from "./update-type";
import {computedFrom} from "aurelia-binding";
import {Router} from "aurelia-router";
import {safeJsonParse} from "../../common/utils/object-utils";
import {Metadata} from "../../resources-config/metadata/metadata";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {ResourceListQuery} from "../resource-list-query";
import {WorkflowRepository} from "workflows/workflow-repository";
import {Workflow} from "workflows/workflow";
import {Alert} from "common/dialog/alert";
import {I18N} from "aurelia-i18n";

@autoinject
export class BulkUpdateSummary {
  resourcesBefore: Resource[];
  resourcesAfter: Resource[];
  totalCount: number;
  loaded: boolean = false;
  change: object;
  action: UpdateType;
  resourceClass: string;
  parentId: number;
  contentsFilter: NumberMap<string>;
  placesFilter: string[];
  kindFilter: number[];
  metadata: Metadata;
  workflow: Workflow;
  private queryParams: object;
  private query: ResourceListQuery;
  private readonly UPDATES_PER_MINUTE = 30;

  constructor(private resourceRepository: ResourceRepository,
              private metadataRepository: MetadataRepository,
              private workflowRepository: WorkflowRepository,
              private alert: Alert,
              private i18n: I18N,
              private router: Router) {
  }

  activate(params) {
    this.queryParams = params;
    this.parentId = +this.queryParams['parentId'];
    this.contentsFilter = safeJsonParse(this.queryParams['contentsFilter']);
    this.placesFilter = safeJsonParse(this.queryParams['placesFilter']);
    this.kindFilter = safeJsonParse(this.queryParams['kindFilter']);
    this.action = params['action'];
    this.change = pick(params, ['metadataId', 'displayStrategy', 'placeId', 'transitionId', 'addValuesAtBeginning', 'workflowId']);
    this.resourceClass = params['resourceClass'];
    this.buildQuery();
    this.fetchMetadata()
      .then(() => this.fetchWorkflow())
      .then(() => this.loadPreview());
  }

  fetchMetadata(): Promise<any> {
    return this.isContentsUpdateSelected
      ? this.metadataRepository.get(this.change['metadataId']).then(m => this.metadata = m)
      : Promise.resolve();
  }

  fetchWorkflow(): Promise<any> {
    return this.isWorkflowUpdateSelected
      ? this.workflowRepository.get(this.change['workflowId']).then(w => this.workflow = w)
      : Promise.resolve();
  }

  @computedFrom("workflow")
  get transitionOrPlaceLabel() {
    if (this.change['transitionId']) {
      return this.workflow.transitions.find(t => t.id == this.change['transitionId']).label;
    } else {
      return this.workflow.places.find(p => p.id == this.change['placeId']).label;
    }
  }

  buildQuery() {
    this.query = this.resourceRepository
      .getListQuery()
      .filterByResourceClasses([this.resourceClass]);
    if (this.kindFilter) {
      this.query.filterByResourceKindIds(this.kindFilter);
    }
    if (this.contentsFilter) {
      this.query.filterByContents(this.contentsFilter);
    }
    if (this.placesFilter) {
      this.query.filterByWorkflowPlacesIds(this.placesFilter);
    }
    if (this.parentId) {
      this.query.filterByParentId(this.parentId);
    } else if (!this.queryParams['allLevels']) {
      this.query.onlyTopLevel();
    }
  }

  resourceAfter(resourceBefore: Resource) {
    return this.resourcesAfter.find(r => r.id == resourceBefore.id);
  }

  @computedFrom('totalCount')
  get predictedTime() {
    return Math.ceil(this.totalCount / this.UPDATES_PER_MINUTE);
  }

  loadPreview() {
    return this.query.setResultsPerPage(10)
      .setCurrentPageNumber(1)
      .get()
      .then(resources => {
        this.resourcesBefore = resources;
        this.totalCount = resources.total;
        const resourceIds = resources.map(r => r.id);
        return this.resourceRepository
          .getBulkUpdatePreview(this.resourceClass, resourceIds, this.action, this.change)
          .then(resources => {
            this.resourcesAfter = resources;
            this.loaded = true;
          });
      });
  }

  @computedFrom('action')
  get isContentsUpdateSelected() {
    return this.action == UpdateType.OVERRIDE || this.action == UpdateType.APPEND;
  }

  @computedFrom('action')
  get isWorkflowUpdateSelected() {
    return this.action == UpdateType.MOVE_TO_PLACE || this.action == UpdateType.EXECUTE_TRANSITION;
  }

  executeUpdate() {
    this.resourceRepository.bulkUpdate(this.resourceClass, this.query, this.action, this.change, this.totalCount)
      .then(() => this.router.navigateToRoute('resources', {resourceClass: this.resourceClass}))
      .then(() => this.alert.show({type: "success"}, this.i18n.tr('Success'), this.i18n.tr('Resource transformations have been queued.')));
  }

  goBack() {
    if (this.parentId) {
      this.queryParams['id'] = this.parentId;
      this.router.navigateToRoute('resources/details', this.queryParams);
    } else {
      this.router.navigateToRoute('resources', this.queryParams);
    }
  }
}
