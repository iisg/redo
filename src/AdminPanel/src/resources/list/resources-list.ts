import {bindingMode, computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {LocalStorage} from "common/utils/local-storage";
import {getQueryParameters} from "common/utils/url-utils";
import {HasRoleValueConverter} from "common/authorization/has-role-value-converter";
import {DisabilityReason} from "common/components/buttons/toggle-button";
import {Alert} from "common/dialog/alert";
import {getMergedBriefMetadata} from "common/utils/metadata-utils";
import {propertyKeys, safeJsonParse} from "common/utils/object-utils";
import {Metadata} from "resources-config/metadata/metadata";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "../context/context-resource-class";
import {PageResult} from "../page-result";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ResourceSort, SortDirection} from "../resource-sort";
import {CurrentUserIsReproductorValueConverter} from "./current-user-is-reproductor";
import {booleanAttribute} from "common/components/boolean-attribute";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";
import {inArray} from "../../common/utils/array-utils";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";

@autoinject()
export class ResourcesList {
  private readonly RESULTS_PER_PAGE_KEY = 'resourcesListElementsPerPage';
  private readonly RESULTS_PER_PAGE_DEFAULT_VALUE = 10;

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
  @bindable @booleanAttribute relationshipChooser = false;
  @observable resources: PageResult<Resource>;
  contentsFilter: NumberMap<string>;
  relatedResources: NumberMap<string>;
  sortBy: ResourceSort[];
  totalNumberOfResources: number;
  addFormOpened: boolean;
  briefMetadata: Metadata[];
  displayProgressBar: boolean;
  activated: boolean;
  private resultsPerPageValueChangedOnActivate: boolean;
  private currentPageNumberChangedOnActivate: boolean;
  private displayAllLevels: boolean = false;
  private sortButtonToggledSubscription: Subscription;
  private resourceFilteredSubscription: Subscription;
  resourceKindIdsAllowedByParent: number[];
  newResourceKind: ResourceKind;
  @observable newResourceKindThrottled: ResourceKind;

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
    // such replacing of resource kind forces the resoruce-form to be rerendered with if.bind
    this.newResourceKind = undefined;
    setTimeout(() => this.newResourceKind = this.newResourceKindThrottled, 100);
  }

  bind() {
    if (this.hasResource()) {
      this.resourceClass = this.getResourceClassFromResource();
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
      this.setResourceKindsAllowedByParent();
    }
    if (this.resourceKind) {
      this.sortButtonToggledSubscription = this.eventAggregator.subscribe('sortButtonToggled',
        (parameters: any) => {
          this.activate(parameters);
        });
      this.resourceFilteredSubscription = this.eventAggregator.subscribe('resourceFiltered',
        (parameters: any) => {
          this.activate(parameters);
        });
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  unbind() {
    if (this.resourceKind) {
      this.sortButtonToggledSubscription.dispose();
      this.resourceFilteredSubscription.dispose();
    }
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
      const isNotSystemRK = resourceKind.id > 0;
      return isAllowedByParent && isNotSystemRK;
    };
  }

  activate(parameters: any) {
    this.prepareBeforeFetchingResources(parameters.resourceClass || this.getResourceClassFromResource());
    this.contextResourceClass.setCurrent(this.resourceClass);
    const resultsPerPageChanged = this.obtainResultsPerPageValue(parameters);
    this.resultsPerPageValueChangedOnActivate = this.activated && resultsPerPageChanged;
    const currentPageNumberChanged = this.obtainCurrentPageNumber(parameters);
    this.currentPageNumberChangedOnActivate = this.activated && currentPageNumberChanged;
    this.contentsFilter = safeJsonParse(parameters['contentsFilter']);
    this.sortBy = safeJsonParse(parameters['sortBy']);
    this.sortBy = this.sortBy ? this.sortBy : this.getSorting();
    LocalStorage.set(`sorting-${this.resourceClass}`, this.sortBy);
    this.displayAllLevels = !!parameters['allLevels'] || !!this.resourceKind;
    if (this.relationshipChooser && (this.relatedResources = safeJsonParse(parameters['relatedResources']))) {
      this.fetchMetadataForMetadataChooser(this.relatedResources);
    } else {
      this.finishActivation();
    }
  }

  private finishActivation() {
    this.fetchResources();
    this.updateURL(true);
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
    this.briefMetadata = [];
    this.fetchBriefMetadata();
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
    this.displayProgressBar = true;
    let resourceClass = this.resourceClass;
    let resultsPerPage = this.resultsPerPage;
    let query = this.resourceRepository.getListQuery();
    if (!this.relationshipChooser || (this.relationshipChooser && this.metadata)) {
      if (this.relationshipChooser && this.relatedResources) {
        query.filterByRelationship(this.relatedResources)
          .suppressError();
      } else if (this.parentResource) {
        query = query.filterByParentId(this.parentResource.id);
      } else if (this.resourceClass) {
        query = query.filterByResourceClasses(this.resourceClass);
        if (!this.displayAllLevels) {
          query = query.onlyTopLevel();
        }
      }
      if (this.resourceKind) {
        query = query.filterByResourceKindIds(this.resourceKind.id);
      }
      if (this.contentsFilter) {
        query = query.filterByContents(this.contentsFilter)
          .suppressError();
      }
      query = query.sortByMetadataIds(this.sortBy)
        .setResultsPerPage(this.resultsPerPage)
        .setCurrentPageNumber(this.currentPageNumber);
      query.get().then(resources => {
        if (resourceClass === this.resourceClass) {
          this.totalNumberOfResources = resources.total;
          if (resources.page === this.currentPageNumber && resultsPerPage === this.resultsPerPage) {
            if (!resources.length && resources.page !== 1) {
              this.currentPageNumber = 1;
            } else {
              this.displayProgressBar = false;
              this.resources = resources;
              this.addFormOpened = this.addFormOpened
                ? this.addFormOpened
                : (this.resources.length == 0) && (this.parentResource == undefined) && !this.contentsFilter;
            }
          }
          if (this.relationshipChooser) {
            this.eventAggregator.publish('relatedResourcesAmount', this.totalNumberOfResources);
          } else if (this.parentResource) {
            this.eventAggregator.publish('resourceChildrenAmount', this.totalNumberOfResources);
          }
        }
      }).catch(error => {
        this.displayProgressBar = false;
        this.resources = new PageResult<Resource>();
        const title = this.i18n.tr("Invalid request");
        const text = this.i18n.tr("The searched phrase is incorrect");
        this.alert.show({type: 'error'}, title, text);
      });
    } else {
      this.displayProgressBar = false;
      this.resources = new PageResult<Resource>();
    }
  }

  fetchBriefMetadata() {
    let query = this.resourceKindRepository.getListQuery();
    if (this.allowedResourceKinds) {
      (this.allowedResourceKinds as Array<ResourceKind | number>).forEach(resourceKindOrId => {
        return resourceKindOrId instanceof ResourceKind
          ? resourceKindOrId.id
          : resourceKindOrId;
      });
    }
    query = (this.allowedResourceKinds && this.allowedResourceKinds.length)
      ? query.filterByIds(this.allowedResourceKinds as number[])
      : query.filterByResourceClasses(this.resourceClass);
    query.get().then(resourceKinds => {
      this.briefMetadata = getMergedBriefMetadata(resourceKinds);
    });
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
      parameters['relatedResources'] = JSON.stringify(this.relatedResources);
    } else if (this.resourceKind) {
      route = 'resource-kinds/details';
      parameters['id'] = this.resourceKind.id;
      parameters['tab'] = 'resources';
    } else {
      route = 'resources';
      parameters['resourceClass'] = this.resourceClass;
    }
    parameters['contentsFilter'] = JSON.stringify(this.contentsFilter);
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

  metadataChanged(newValue: Metadata, oldValue: Metadata) {
    if (newValue && (!oldValue || oldValue.id != newValue.id)) {
      this.relatedResources = {};
      this.relatedResources[newValue.id.toString()] = this.resource.id;
      this.updateURL(true);
      this.fetchResources();
    }
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

  getResourceClassFromResource(): string {
    return (this.parentResource && this.parentResource.resourceClass) ||
      (this.resource && this.resource.resourceClass) ||
      this.resourceClass;
  }

  private getSortingFromLocalStorage() {
    try {
      return safeJsonParse(localStorage.getItem(`sorting-${this.resourceClass}`));
    } catch (e) {
      return this.sortBy;
    }
  }

  hasResource() {
    return this.resource || this.parentResource;
  }

  private getSorting(): ResourceSort[] {
    const cachedSorting = this.getSortingFromLocalStorage();
    const language = this.i18n.getLocale().toUpperCase();
    return cachedSorting ? cachedSorting : [new ResourceSort('id', SortDirection.DESC, language)];
  }

  private fetchMetadataForMetadataChooser(relatedResources: any) {
    const id = relatedResources && propertyKeys(relatedResources)[0];
    if (id && this.relationshipChooser) {
      this.metadataRepository.get(id).then(metadata => {
        this.metadata = metadata;
        this.finishActivation();
      });
    }
  }
}
