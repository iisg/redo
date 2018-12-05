import {bindingMode, computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {LocalStorage} from "common/utils/local-storage";
import {getQueryParameters} from "common/utils/url-utils";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";
import {DisabilityReason} from "../../common/components/buttons/toggle-button";
import {Alert} from "../../common/dialog/alert";
import {getMergedBriefMetadata} from "../../common/utils/metadata-utils";
import {safeJsonParse} from "../../common/utils/object-utils";
import {Metadata} from "../../resources-config/metadata/metadata";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "../context/context-resource-class";
import {PageResult} from "../page-result";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ResourceSort, SortDirection} from "../resource-sort";
import {CurrentUserIsReproductorValueConverter} from "./current-user-is-reproductor";

@autoinject()
export class ResourcesList {
  private readonly RESULTS_PER_PAGE_KEY = 'resourcesListElementsPerPage';
  private readonly RESULTS_PER_PAGE_DEFAULT_VALUE = 10;

  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;
  @bindable resourceClass: string;
  @bindable disableAddResource: boolean;
  @bindable resultsPerPage: number;
  @bindable currentPageNumber: number;
  @bindable allowedResourceKinds: number[] | ResourceKind[];
  @observable resources: PageResult<Resource>;
  contentsFilter: NumberMap<string>;
  sortBy: ResourceSort[];
  totalNumberOfResources: number;
  addFormOpened: boolean;
  briefMetadata: Metadata[];
  displayProgressBar: boolean;
  activated: boolean;
  private resultsPerPageValueChangedOnActivate: boolean;
  private currentPageNumberChangedOnActivate: boolean;
  private displayAllLevels: boolean = false;

  constructor(private alert: Alert,
              private i18n: I18N,
              private contextResourceClass: ContextResourceClass,
              private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private eventAggregator: EventAggregator,
              private router: Router,
              private hasRole: HasRoleValueConverter,
              private isReproductor: CurrentUserIsReproductorValueConverter) {
  }

  bind() {
    if (this.parentResource) {
      this.resourceClass = this.parentResource.resourceClass;
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
    }
  }

  activate(parameters: any) {
    this.prepareBeforeFetchingResources(parameters.resourceClass || this.parentResource.resourceClass);
    this.contextResourceClass.setCurrent(this.resourceClass);
    const resultsPerPageChanged = this.obtainResultsPerPageValue(parameters);
    this.resultsPerPageValueChangedOnActivate = this.activated && resultsPerPageChanged;
    const currentPageNumberChanged = this.obtainCurrentPageNumber(parameters);
    this.currentPageNumberChangedOnActivate = this.activated && currentPageNumberChanged;
    this.contentsFilter = safeJsonParse(parameters['contentsFilter']);
    this.sortBy = safeJsonParse(parameters['sortBy']);
    this.sortBy = this.sortBy ? this.sortBy : this.getSorting();
    this.displayAllLevels = !!parameters['allLevels'];
    LocalStorage.set(`sorting-${this.resourceClass}`, this.sortBy);
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
    }
    else {
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
    if (this.parentResource) {
      query = query.filterByParentId(this.parentResource.id);
    } else {
      query = query.filterByResourceClasses(this.resourceClass);
      if (!this.displayAllLevels) {
        query = query.onlyTopLevel();
      }
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter)
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
      }
    }).catch(error => {
      this.displayProgressBar = false;
      this.resources = new PageResult<Resource>();
      const title = this.i18n.tr("Invalid request");
      const text = this.i18n.tr("The searched phrase is incorrect");
      this.alert.show({type: 'error'}, title, text);
    });
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

  private getSorting(): ResourceSort[] {
    const cachedSorting = LocalStorage.get(`sorting-${this.resourceClass}`);
    const language = this.i18n.getLocale().toUpperCase();
    return cachedSorting ? cachedSorting : [new ResourceSort('id', SortDirection.DESC, language)];
  }
}
