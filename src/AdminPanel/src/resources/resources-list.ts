import {bindingMode, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {Router, NavigationInstruction} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {Metadata} from "../resources-config/metadata/metadata";
import {ResourceKindRepository} from "../resources-config/resource-kind/resource-kind-repository";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";
import {PageResult} from "./page-result";
import {ContextResourceClass} from "./context/context-resource-class";
import {ResourceMetadataSort} from "./resource-metadata-sort";
import {safeJsonParse} from "../common/utils/object-utils";

@autoinject
export class ResourcesList {
  private readonly RESULTS_PER_PAGE_KEY = 'resourcesListElementsPerPage';

  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;
  @bindable resourceClass: string;
  @observable resources: PageResult<Resource>;
  @observable resultsPerPage: number;
  @observable currentPageNumber: number;
  contentsFilter: NumberMap<string>;
  sortBy: ResourceMetadataSort[];
  totalNumberOfResources: number;
  addFormOpened: boolean;
  briefMetadata: Metadata[];
  displayProgressBar: boolean;
  activated: boolean;

  constructor(private contextResourceClass: ContextResourceClass,
              private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private eventAggregator: EventAggregator,
              private router: Router) {
  }

  activate(parameters: any) {
    this.activated = false;
    this.prepareBeforeFetchingResources(parameters.resourceClass || this.parentResource.resourceClass);
    this.contextResourceClass.setCurrent(this.resourceClass);
    this.obtainResultsPerPageValue(parameters);
    this.obtainCurrentPageNumber(parameters);
    this.obtainContentsFilterValue(parameters);
    this.obtainSortByValue(parameters);
    this.fetchResources();
    this.activated = true;
  }

  private obtainResultsPerPageValue(parameters: any) {
    if (parameters.resourcesPerPage > 0) {
      this.resultsPerPage = parseInt(parameters.resourcesPerPage);
    }
    else {
      try {
        this.resultsPerPage = localStorage[this.RESULTS_PER_PAGE_KEY] ? parseInt(localStorage[this.RESULTS_PER_PAGE_KEY]) : 10;
      }
      catch (exception) {
        this.resultsPerPage = 10;
      }
    }
  }

  private obtainCurrentPageNumber(parameters: any) {
    if (parameters.currentPageNumber > 0) {
      this.currentPageNumber = parseInt(parameters.currentPageNumber);
    }
    else if (this.currentPageNumber == 1) {
      this.updateURL();
    }
    else {
      this.currentPageNumber = 1;
    }
  }

  private obtainContentsFilterValue(parameters: any) {
    let contentsFilter = safeJsonParse(parameters['contentsFilter']);
    if (this.contentsFilter != contentsFilter) {
      this.contentsFilter = contentsFilter;
      this.updateURL();
    }
  }

  private obtainSortByValue(parameters: any) {
    let sortBy = safeJsonParse(parameters['sortBy']);
    if (this.sortBy != sortBy) {
      this.sortBy = sortBy;
      this.updateURL();
    }
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
    query = this.parentResource ? query.filterByParentId(this.parentResource.id)
      : query.onlyTopLevel()
      .filterByResourceClasses(this.resourceClass);
    query = query.sortByMetadataIds(this.sortBy)
      .setResultsPerPage(this.resultsPerPage)
      .setPage(this.currentPageNumber);
    if (this.contentsFilter) {
      query = query.filterByContents(this.contentsFilter);
    }
    query.get()
      .then(resources => {
        if (resourceClass === this.resourceClass) {
          this.totalNumberOfResources = resources.total;
          if (resources.page === this.currentPageNumber && resultsPerPage === this.resultsPerPage) {
            if (!resources.length && resources.page !== 1) {
              this.currentPageNumber = 1;
            } else {
              this.displayProgressBar = false;
              this.resources = resources;
              this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined) && !this.contentsFilter;
            }
          }
        }
      });
  }

  fetchBriefMetadata() {
    this.resourceKindRepository.getListByClass(this.resourceClass).then(resourceKinds => {
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
    if (previousValue) {
      try {
        localStorage[this.RESULTS_PER_PAGE_KEY] = newValue;
      } catch (exception) {}
      this.updateURL();
      if (this.activated) {
        this.fetchResources();
      }
    }
  }

  currentPageNumberChanged() {
    this.updateURL(!this.activated);
    if (this.activated) {
      this.fetchResources();
    }
  }

  updateURL(replaceEntryInBrowserHistory = true) {
    let route: string;
    let parameters = {};
    if (this.parentResource) {
      route = 'resources/details';
      parameters['id'] = this.parentResource.id;
    } else {
      route = 'resources';
      parameters['resourceClass'] = this.resourceClass;
    }
    parameters['contentsFilter'] = JSON.stringify(this.contentsFilter);
    parameters['sortBy'] = JSON.stringify(this.sortBy);
    parameters['resourcesPerPage'] = this.resultsPerPage;
    parameters['currentPageNumber'] = this.currentPageNumber;
    this.router.navigateToRoute(route, parameters, {trigger: false, replace: replaceEntryInBrowserHistory});
  }
}
