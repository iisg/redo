import {bindingMode, computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {HasRoleValueConverter} from "common/authorization/has-role-value-converter";
import {booleanAttribute} from "common/components/boolean-attribute";
import {DisabilityReason} from "common/components/buttons/toggle-button";
import {Alert} from "common/dialog/alert";
import {LocalStorage} from "common/utils/local-storage";
import {deepCopy, safeJsonParse} from "common/utils/object-utils";
import {getQueryParameters} from "common/utils/url-utils";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {inArray} from "common/utils/array-utils";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {ContextResourceClass} from "../context/context-resource-class";
import {PageResult} from "../page-result";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ResourceSort, SortDirection} from "../resource-sort";
import {CurrentUserIsReproductorValueConverter} from "./current-user-is-reproductor";
import {FilterChangedEvent} from "resources/list/resources-list-filters";
import {MetadataFilterChange} from "resources/list/resource-list-filters/resource-list-metadata-filter/resource-list-metadata-filter";
import {PlacesFilterChange} from "resources/list/resource-list-filters/resource-list-place-filter/resource-list-place-filter";
import {KindsFilterChange} from "./resource-list-filters/resource-list-kind-filter/resource-list-kind-filter";
import {ResourceListQuery} from "../resource-list-query";
import {pick} from 'lodash';

@autoinject()
export class ResourcesList {
  private readonly RESULTS_PER_PAGE_KEY = 'resourcesListElementsPerPage';
  private readonly RESULTS_PER_PAGE_DEFAULT_VALUE = 10;
  private readonly SORT_BY_KEY_PREFIX = 'sorting-';
  private readonly DEFAULT_SORTING = [new ResourceSort('id', SortDirection.DESC, this.i18n.getLocale().toUpperCase())];

  @bindable parentResource: Resource = undefined;
  @bindable resource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;
  @bindable resourceClass: string;
  @bindable disableAddResource: boolean;
  @bindable resultsPerPage: number;
  @bindable currentPageNumber: number;
  @bindable allowedResourceKinds: number[] | ResourceKind[];
  @bindable resourceKind: ResourceKind;
  @bindable metadata: Metadata;
  @bindable @booleanAttribute hideAddButton = false;
  @observable resources: PageResult<Resource>;
  @observable newResourceKindThrottled: ResourceKind;
  query: ResourceListQuery;
  contentsFilter: NumberMap<string>;
  idsFilter: string;
  placesFilter: string[];
  sortBy: ResourceSort[];
  totalNumberOfResources: number;
  addFormOpened: boolean;
  bulkUpdateFormOpened: boolean;
  update: object;
  resourceKinds: ResourceKind[] = [];
  displayProgressBar: boolean;
  activated: boolean;
  newResourceKind: ResourceKind;
  private resultsPerPageValueChangedOnActivate: boolean;
  private currentPageNumberChangedOnActivate: boolean;
  private displayAllLevels: boolean = false;
  private eventSubscriptions: Subscription[] = [];
  private resourceKindIdsAllowedByParent: number[];
  private sortByKey: string;
  private kindFilter: number[];

  constructor(private alert: Alert,
              private i18n: I18N,
              private contextResourceClass: ContextResourceClass,
              private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private metadataRepository: MetadataRepository,
              private eventAggregator: EventAggregator,
              private router: Router,
              private hasRole: HasRoleValueConverter,
              private isReproductor: CurrentUserIsReproductorValueConverter) {
  }

  newResourceKindThrottledChanged() {
    // Replacing resource kind forces the `resource-form` to be rerendered with `if.bind`.
    this.newResourceKind = undefined;
    setTimeout(() => this.newResourceKind = this.newResourceKindThrottled, 100);
  }

  bind() {
    if (this.hasResource()) {
      this.resourceClass = this.getResourceClass();
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
      this.setResourceKindsAllowedByParent();
    }
    this.sortByKey = this.SORT_BY_KEY_PREFIX + this.resourceClass;
    this.eventSubscriptions.push(
      this.eventAggregator.subscribe('sortButtonToggled',
        ({value}: FilterChangedEvent<ResourceSort>) => {
          this.sortButtonToggled(value);
        })
    );
    this.eventSubscriptions.push(
      this.eventAggregator.subscribe('metadataFilterValueChanged',
        ({value}: FilterChangedEvent<MetadataFilterChange>) => {
          this.metadataFilterValueChanged(value);
        })
    );
    this.eventSubscriptions.push(
      this.eventAggregator.subscribe('placeFilterValueChanged',
        ({value}: FilterChangedEvent<PlacesFilterChange>) => {
          this.placeFilterValueChanged(value);
        }
      )
    );
    this.eventSubscriptions.push(
      this.eventAggregator.subscribe('kindFilterValueChanged',
        ({value}: FilterChangedEvent<KindsFilterChange>) => {
          this.kindFilterValueChanged(value);
        }
      )
    );
    if (this.resourceKind) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  unbind() {
    this.eventSubscriptions.forEach(s => s.dispose());
  }

  private setResourceKindsAllowedByParent() {
    this.resourceKindIdsAllowedByParent = undefined;
    if (this.parentResource) {
      let metadata = this.parentResource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
      let resourceKindsAllowedByParent: any[] = metadata.constraints.resourceKind;
      this.resourceKindIdsAllowedByParent = resourceKindsAllowedByParent.map(v => v.id || v);
    }
  }

  createResourceKindFilter() {
    return (resourceKind: ResourceKind) => {
      const isAllowedByParent = !Array.isArray(this.resourceKindIdsAllowedByParent)
        || inArray(resourceKind.id, this.resourceKindIdsAllowedByParent);
      const isNotSystemResourceKind = resourceKind.id > 0;
      return isAllowedByParent && isNotSystemResourceKind;
    };
  }

  private sortButtonToggled(resourceSort: ResourceSort) {
    this.sortBy = resourceSort ? [resourceSort] : this.DEFAULT_SORTING;
    this.fetchList();
    LocalStorage.set(this.sortByKey, this.sortBy);
  }

  private metadataFilterValueChanged(metadataIdWithValue) {
    if (!this.contentsFilter) {
      this.contentsFilter = {};
    }
    this.contentsFilter[metadataIdWithValue.metadataId] = metadataIdWithValue.value;
    this.contentsFilter = deepCopy(this.contentsFilter);
    this.fetchList();
  }

  fetchList() {
    this.updateURL(true);
    this.fetchResources();
  }

  private placeFilterValueChanged(placesIds: string[]) {
    if (!this.placesFilter) {
      this.placesFilter = [];
    }
    this.placesFilter = placesIds;
    this.fetchList();
  }

  private kindFilterValueChanged(resourceKinds: number[]) {
    if (!this.kindFilter) {
      this.kindFilter = [];
    }
    this.kindFilter = resourceKinds;
    this.fetchList();
  }

  activate(parameters: any) {
    this.newResourceKind = undefined;
    this.prepareBeforeFetchingResources(parameters.resourceClass || this.getResourceClass());
    this.sortByKey = this.SORT_BY_KEY_PREFIX + this.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
    const resultsPerPageChanged = this.obtainResultsPerPageValue(parameters);
    this.resultsPerPageValueChangedOnActivate = this.activated && resultsPerPageChanged;
    const currentPageNumberChanged = this.obtainCurrentPageNumber(parameters);
    this.currentPageNumberChangedOnActivate = this.activated && currentPageNumberChanged;
    this.contentsFilter = safeJsonParse(parameters['contentsFilter']) || {};
    this.idsFilter = parameters['ids'];
    this.placesFilter = safeJsonParse(parameters['placesFilter']);
    this.kindFilter = safeJsonParse(parameters['kindFilter']);
    let sortBy = safeJsonParse(parameters['sortBy']);
    this.sortBy = sortBy ? sortBy : this.getSorting();
    this.displayAllLevels = !!parameters['allLevels'] || !!this.resourceKind;
    if (parameters['action']) {
      this.update =  pick(parameters, ['action', 'displayStrategy', 'metadataId', 'workflow', 'placeId', 'transitionId']);
      this.bulkUpdateFormOpened = true;
    }
    if (this.metadata) {
      this.updateContentsFilter(this.metadata);
    } else {
      this.fetchList();
    }
    this.activated = true;
  }

  attached() {
    if (!this.activated) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  private prepareBeforeFetchingResources(resourceClass: string) {
    this.resourceClass = resourceClass;
    this.totalNumberOfResources = undefined;
    if (this.resources) {
      this.resources = new PageResult<Resource>();
    }
    this.addFormOpened = false;
    this.fetchPossibleResourceKinds();
  }

  private obtainResultsPerPageValue(parameters: any): boolean {
    let resultsPerPage: number;
    if (parameters.resourcesPerPage > 0) {
      resultsPerPage = parseInt(parameters.resourcesPerPage);
    } else {
      resultsPerPage = LocalStorage.get(this.RESULTS_PER_PAGE_KEY);
      if (!resultsPerPage) {
        resultsPerPage = this.RESULTS_PER_PAGE_DEFAULT_VALUE;
      }
    }
    const resultsPerPageChanged = this.resultsPerPage != resultsPerPage;
    this.resultsPerPage = resultsPerPage;
    return resultsPerPageChanged;
  }

  private obtainCurrentPageNumber(parameters: any): boolean {
    let currentPageNumber: number;
    if (parameters.currentPageNumber > 0) {
      currentPageNumber = parseInt(parameters.currentPageNumber);
    } else {
      currentPageNumber = 1;
    }
    const currentPageNumberChanged = this.currentPageNumber != currentPageNumber;
    this.currentPageNumber = currentPageNumber;
    return currentPageNumberChanged;
  }

  fetchResources() {
    let contentsFilter = this.contentsFilter;
    let resourceClass = this.resourceClass;
    let resultsPerPage = this.resultsPerPage;
    this.query = this.resourceRepository.getListQuery();
    this.displayProgressBar = true;
    if (this.parentResource) {
      this.query = this.query.filterByParentId(this.parentResource.id);
    } else if (this.resourceClass && !this.resource) {
      this.query = this.query.filterByResourceClasses(this.resourceClass);
      if (!this.displayAllLevels) {
        this.query = this.query.onlyTopLevel();
      }
    }
    if (this.resourceKind) {
      this.query = this.query.filterByResourceKindIds(this.resourceKind.id);
    } else if (!this.resourceKind && this.kindFilter && this.kindFilter.length) {
      this.query = this.query.filterByResourceKindIds(this.kindFilter);
    }
    if (this.contentsFilter && Object.values(this.contentsFilter).find(value => value !== undefined)) {
      this.query = this.query.filterByContents(this.contentsFilter).suppressError();
    }
    if (this.idsFilter) {
      this.query = this.query.filterByIds(this.idsFilter);
    }
    if (this.placesFilter && this.placesFilter.length) {
      this.query = this.query.filterByWorkflowPlacesIds(this.placesFilter);
    }
    this.query = this.query.sortByMetadataIds(this.sortBy)
      .setResultsPerPage(this.resultsPerPage)
      .setCurrentPageNumber(this.currentPageNumber);
    this.query.get().then(resources => {
      if (resources.total === 1 && this.idsFilter) {
        const id = resources[0].id;
        this.router.navigateToRoute('resources/details', {id}, {trigger: true, replace: false});
      } else if (resourceClass == this.resourceClass && contentsFilter == this.contentsFilter) {
        this.totalNumberOfResources = resources.total;
        if (resources.page == this.currentPageNumber && resultsPerPage == this.resultsPerPage) {
          if (!resources.length && resources.page !== 1) {
            this.currentPageNumber = 1;
          } else {
            this.resources = resources;
            this.addFormOpened = this.addFormOpened
              ||  (this.resources.length == 0 && this.parentResource == undefined && !this.contentsFilter);
          }
        }
        if (this.parentResource || this.resourceKind) {
          this.eventAggregator.publish('resourceChildrenAmount', this.totalNumberOfResources);
        }
      }
    }).catch(error => {
      this.resources = new PageResult<Resource>();
      const title = this.i18n.tr("Invalid request");
      const text = this.i18n.tr("The searched phrase is incorrect");
      this.alert.show({type: 'error'}, title, text);
    }).finally(() => this.displayProgressBar = false);
  }

  fetchPossibleResourceKinds() {
    if (this.resourceKind) {
      this.resourceKinds = [this.resourceKind];
    } else {
      let query = this.resourceKindRepository.getListQuery();
      if (this.allowedResourceKinds) {
        (this.allowedResourceKinds as Array<ResourceKind | number>).forEach(resourceKindOrId => {
          return resourceKindOrId instanceof ResourceKind
            ? resourceKindOrId.id
            : resourceKindOrId;
        });
      }
      if (this.allowedResourceKinds && this.allowedResourceKinds.length) {
        query.filterByIds(this.allowedResourceKinds as number[]);
      } else if (this.resourceClass) {
        query.filterByResourceClasses(this.resourceClass);
      }
      query.get().then(resourceKinds => {
        // this.resourceKinds should stay the same instance. Reassigning causes no detection of list changes in resource-list-places-filter
        this.resourceKinds.splice(0, this.resourceKinds.length);
        this.resourceKinds.push(...resourceKinds);
      });
    }
  }

  resourceClassChanged() {
    this.sortByKey = this.SORT_BY_KEY_PREFIX + this.resourceClass;
  }

  resourcesChanged(newResources: Resource[]) {
    this.hasResources = newResources.length > 0;
  }

  addNewResource(resource: Resource): Promise<any> {
    resource.resourceClass = this.resourceClass;
    return this.resourceRepository.post(resource).then(resource => {
      this.router.navigateToRoute('resources/details', {id: resource.id});
    });
  }

  resultsPerPageChanged(newValue: number, previousValue: number) {
    if (!this.resultsPerPageValueChangedOnActivate && previousValue) {
      LocalStorage.set(this.RESULTS_PER_PAGE_KEY, newValue);
      if (this.activated && this.currentPageNumber == 1) {
        this.updateURL();
        this.fetchResources();
      }
    }
    this.resultsPerPageValueChangedOnActivate = false;
  }

  currentPageNumberChanged(newValue: number, previousValue: number) {
    if (!this.currentPageNumberChangedOnActivate && previousValue) {
      this.updateURL();
      this.fetchResources();
    }
    this.currentPageNumberChangedOnActivate = false;
  }

  updateURL(replaceEntryInBrowserHistory?: boolean) {
    let route: string;
    const queryParameters = getQueryParameters(this.router);
    const parameters = {};
    parameters['tab'] = queryParameters['tab'];
    if (this.parentResource) {
      route = 'resources/details';
      parameters['id'] = this.parentResource.id;
      parameters['tab'] = 'children';
    } else if (this.resource) {
      route = 'resources/details';
      parameters['id'] = this.resource.id;
      parameters['tab'] = 'relationships';
    } else if (this.resourceKind) {
      route = 'resource-kinds/details';
      parameters['id'] = this.resourceKind.id;
      parameters['tab'] = 'resources';
    } else {
      route = 'resources';
      parameters['resourceClass'] = this.resourceClass;
    }
    if (this.contentsFilter && Object.values(this.contentsFilter).find(value => value !== undefined)) {
      parameters['contentsFilter'] = JSON.stringify(this.contentsFilter);
    }
    if (this.idsFilter) {
      parameters['ids'] = this.idsFilter;
    }
    if (this.placesFilter && this.placesFilter.length) {
      parameters['placesFilter'] = JSON.stringify(this.placesFilter);
    }
    if (this.kindFilter && this.kindFilter.length) {
      parameters['kindFilter'] = JSON.stringify(this.kindFilter);
    }
    parameters['sortBy'] = JSON.stringify(this.sortBy);
    if (!queryParameters['tab'] || queryParameters['tab'] == 'children') {
      parameters['resourcesPerPage'] = this.resultsPerPage;
      parameters['currentPageNumber'] = this.currentPageNumber;
    }
    if (this.displayAllLevels) {
      parameters['allLevels'] = true;
    }
    this.router.navigateToRoute(route, parameters, {trigger: false, replace: replaceEntryInBrowserHistory});
  }

  @computedFrom("disableAddResource", "parentResource", "parentResource.pendingRequest")
  get addingResourcesDisabled(): boolean {
    return this.disableAddResource
      || (this.parentResource && (this.parentResource.pendingRequest || !this.isReproductor.toView(this.parentResource)))
      || (!this.parentResource && !this.hasRole.toView('ADMIN', this.resourceClass));
  }

  @computedFrom("disabledAddResource", "parentResource", "resourceClass")
  get disabilityReason(): DisabilityReason {
    const hasNoPermissions = (this.parentResource && !this.isReproductor.toView(this.parentResource))
      || (!this.parentResource && !this.hasRole.toView('ADMIN', this.resourceClass));
    if (hasNoPermissions) {
      return {icon: 'user-2', message: 'You do not have permissions to add resource.'};
    }
    if (this.disableAddResource) {
      return {icon: 'help', message: 'Resource kind does not allow to add resource.'};
    }
    return undefined;
  }

  getResourceClass(): string {
    return (this.parentResource && this.parentResource.resourceClass)
      || (this.metadata && this.metadata.resourceClass);
  }

  hasResource() {
    return this.resource || this.parentResource;
  }

  private getSorting(): ResourceSort[] {
    const cachedSorting = LocalStorage.get(this.sortByKey);
    return cachedSorting ? cachedSorting : this.DEFAULT_SORTING;
  }

  metadataChanged(newValue: Metadata, oldValue: Metadata) {
    this.resourceClass = this.metadata.resourceClass;
    this.fetchPossibleResourceKinds();
    this.updateContentsFilter(newValue, oldValue);
  }

  private updateContentsFilter(newValue: Metadata, oldValue: Metadata = undefined) {
    let newContentFilters: NumberMap<string> = {};
    newContentFilters[newValue.id] = this.resource.id.toString();
    for (const [metadataId, value] of Object.entries(this.contentsFilter)) {
      if ((!oldValue || oldValue.id != +metadataId) && (!newValue || newValue.id != +metadataId)) {
        newContentFilters[metadataId] = value;
      }
    }
    this.contentsFilter = newContentFilters;
    this.fetchList();
  }

  switchToAllResourcesView() {
    this.displayAllLevels = true;
    this.fetchList();
  }

  switchToTopResourcesView() {
    this.displayAllLevels = false;
    this.fetchList();
  }

  @computedFrom('addFormOpened', 'bulkUpdateFormOpened')
  get isFormOpened() {
    return this.addFormOpened || this.bulkUpdateFormOpened;
  }
}
