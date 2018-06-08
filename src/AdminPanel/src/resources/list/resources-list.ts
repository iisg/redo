import {bindingMode, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {parseQueryString} from "aurelia-path";
import {NavigationInstruction, Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {PageResult} from "../page-result";
import {ResourceSort, SortDirection} from "../resource-sort";
import {Metadata} from "../../resources-config/metadata/metadata";
import {ContextResourceClass} from "../context/context-resource-class";
import {ResourceRepository} from "../resource-repository";
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";
import {safeJsonParse} from "../../common/utils/object-utils";
import {getMergedBriefMetadata} from "../../common/utils/metadata-utils";

@autoinject
export class ResourcesList {
  private readonly RESULTS_PER_PAGE_KEY = 'resourcesListElementsPerPage';

  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;
  @bindable resourceClass: string;
  @bindable disableAddResource: boolean;
  @bindable resultsPerPage: number;
  @bindable currentPageNumber: number;
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

  constructor(private contextResourceClass: ContextResourceClass,
              private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private eventAggregator: EventAggregator,
              private router: Router) {
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
    this.sortBy = this.sortBy ? this.sortBy : [new ResourceSort('id', SortDirection.DESC)];
    this.updateURL(true);
    this.fetchResources();
    this.activated = true;
  }

  private obtainResultsPerPageValue(parameters: any): boolean {
    let resultsPerPage: number;
    if (parameters.resourcesPerPage > 0) {
      resultsPerPage = parseInt(parameters.resourcesPerPage);
    }
    else {
      try {
        const resultsPerPageValueFromLocalStorage = localStorage[this.RESULTS_PER_PAGE_KEY];
        resultsPerPage = resultsPerPageValueFromLocalStorage > 0 ? parseInt(resultsPerPageValueFromLocalStorage) : 10;
      }
      catch (exception) {
        resultsPerPage = 10;
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

  bind() {
    if (this.parentResource) {
      this.resourceClass = this.parentResource.resourceClass;
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
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

  fetchResources() {
    this.displayProgressBar = true;
    let resourceClass = this.resourceClass;
    let resultsPerPage = this.resultsPerPage;
    let query = this.resourceRepository.getListQuery();
    if (this.parentResource) {
      query = query.filterByParentId(this.parentResource.id);
    } else {
      query = query.onlyTopLevel()
        .filterByResourceClasses(this.resourceClass);
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter);
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
    });
  }

  fetchBriefMetadata() {
    this.resourceKindRepository.getListQuery().filterByResourceClasses(this.resourceClass).get().then(resourceKinds => {
      this.briefMetadata = getMergedBriefMetadata(resourceKinds);
    });
  }

  resourcesChanged(newResources: Resource[]) {
    this.hasResources = newResources.length > 0;
  }

  addNewResource(resource: Resource): Promise<Resource> {
    resource.resourceClass = this.resourceClass;
    return this.resourceRepository.post(resource).then(resource => {
      this.addFormOpened = false;
      let lastPageNumber = Math.ceil((this.totalNumberOfResources + 1) / this.resultsPerPage);
      if (this.currentPageNumber == lastPageNumber) {
        this.resources.push(resource);
      } else {
        this.currentPageNumber = lastPageNumber;
      }
      return resource;
    });
  }

  resultsPerPageChanged(newValue: number, previousValue: number) {
    if (!this.resultsPerPageValueChangedOnActivate && previousValue) {
      try {
        localStorage[this.RESULTS_PER_PAGE_KEY] = newValue;
      } catch (exception) {
      }
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
    const href = window.location.href;
    const currentParameters = parseQueryString(href.slice(href.indexOf('?')));
    const parameters = {};
    parameters['tab'] = currentParameters['tab'];
    if (this.parentResource) {
      route = 'resources/details';
      parameters['id'] = this.parentResource.id;
    } else {
      route = 'resources';
      parameters['resourceClass'] = this.resourceClass;
    }
    parameters['contentsFilter'] = JSON.stringify(this.contentsFilter);
    parameters['sortBy'] = JSON.stringify(this.sortBy);
    if (!currentParameters['tab'] || currentParameters['tab'] == 'children') {
      parameters['resourcesPerPage'] = this.resultsPerPage;
      parameters['currentPageNumber'] = this.currentPageNumber;
    }
    this.router.navigateToRoute(route, parameters, {trigger: false, replace: replaceEntryInBrowserHistory});
  }
}
