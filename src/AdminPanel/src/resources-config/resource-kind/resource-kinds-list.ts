import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {ContextResourceClass} from "resources/context/context-resource-class";
import {booleanAttribute} from "common/components/boolean-attribute";
import {safeJsonParse} from "common/utils/object-utils";
import {getQueryParameters} from "common/utils/url-utils";
import {ResourceSort, SortDirection} from "resources/resource-sort";
import {Metadata} from "../metadata/metadata";
import {ResourceKind} from "./resource-kind";
import {ResourceKindRepository} from "./resource-kind-repository";
import {FilterChangedEvent} from "resources/list/resources-list-filters";

@autoinject
export class ResourceKindsList {
  private readonly DEFAULT_SORTING: ResourceSort[];

  @bindable @booleanAttribute hideAddButton = false;
  @bindable resourceClass: string;
  @bindable sortable = true;
  @bindable metadata: Metadata;
  resourceKinds: ResourceKind[];
  addFormOpened = false;
  progressBar: boolean;
  sortBy: ResourceSort[];
  private sortButtonToggledSubscription: Subscription;

  constructor(private resourceKindRepository: ResourceKindRepository,
              private contextResourceClass: ContextResourceClass,
              private router: Router,
              private eventAggregator: EventAggregator,
              i18n: I18N) {
    this.DEFAULT_SORTING = [new ResourceSort('id', SortDirection.DESC, i18n.getLocale().toUpperCase())];
  }

  bind() {
    this.sortButtonToggledSubscription = this.eventAggregator.subscribe('sortButtonToggled',
      ({value}: FilterChangedEvent<ResourceSort>) => {
        this.sortButtonToggled(value);
      });
    if (this.metadata) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  activate(parameters: any) {
    this.resourceClass = parameters.resourceClass || (this.metadata && this.metadata.resourceClass) || this.resourceClass;
    this.contextResourceClass.setCurrent(this.resourceClass);
    let sortBy = safeJsonParse(parameters['sortBy']);
    this.sortBy = sortBy ? sortBy : this.DEFAULT_SORTING;
    if (this.resourceKinds && !this.metadata) {
      this.resourceKinds = [];
    }
    this.updateURL(true);
    this.fetchResourceKinds();
  }

  unbind() {
    if (this.sortButtonToggledSubscription) {
      this.sortButtonToggledSubscription.dispose();
    }
  }

  sortButtonToggled(resourceSort: ResourceSort) {
    this.sortBy = resourceSort ? [resourceSort] : this.DEFAULT_SORTING;
    this.updateURL(true);
    this.fetchResourceKinds();
  }

  fetchResourceKinds() {
    this.progressBar = true;
    let query = this.resourceKindRepository.getListQuery()
      .filterByResourceClasses(this.resourceClass)
      .sortByMetadataIds(this.sortBy);
    if (this.metadata) {
      query.filterByMetadataId(this.metadata.id);
    }
    query.get()
      .then(resourceKinds => {
        this.progressBar = false;
        this.resourceKinds = resourceKinds;
        this.addFormOpened = this.resourceKinds.length == 0;
      });
  }

  addNewResourceKind(resourceKind: ResourceKind): Promise<any> {
    resourceKind.resourceClass = this.resourceClass;
    return this.resourceKindRepository.post(resourceKind).then(resourceKind => {
      this.router.navigateToRoute('resource-kinds/details', {id: resourceKind.id});
    });
  }

  toggleEditForm() {
    this.addFormOpened = !this.addFormOpened;
  }

  updateURL(replaceEntryInBrowserHistory?: boolean) {
    let route: string;
    const queryParameters = getQueryParameters();
    const parameters = {};
    parameters['tab'] = queryParameters['tab'];
    if (this.metadata) {
      route = 'metadata/details';
      parameters['id'] = this.metadata.id;
    } else {
      route = 'resource-kinds';
      parameters['resourceClass'] = this.resourceClass;
    }
    parameters['sortBy'] = JSON.stringify(this.sortBy);
    this.router.navigateToRoute(route, parameters, {trigger: false, replace: replaceEntryInBrowserHistory});
  }

  detached() {
    this.metadata = undefined;
  }
}
