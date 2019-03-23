import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {Alert} from "common/dialog/alert";
import {LocalStorage} from "common/utils/local-storage";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ResourceKindRepository} from "resources-config/resource-kind/resource-kind-repository";
import {ContextResourceClass} from "../context/context-resource-class";
import {PageResult} from "../page-result";
import {Resource} from "../resource";
import {ResourceRepository} from "../resource-repository";
import {ResourceSort, SortDirection} from "../resource-sort";
import {FilterChangedEvent, ResourcesListFilters} from "./resources-list-filters";
import {oneTime} from "common/components/binding-mode";
import {MetadataFilterChange} from "./resource-list-filters/resource-list-metadata-filter/resource-list-metadata-filter";
import {debounce} from "lodash";

@autoinject()
export class ResourcesListPaginated {
  private readonly RESULTS_PER_PAGE_KEY_PREFIX = 'resourcesPerPage-';
  private readonly SORT_BY_KEY_PREFIX = 'sorting-';
  private readonly DEFAULT_SORTING = [new ResourceSort('id', SortDirection.DESC, this.i18n.getLocale().toUpperCase())];

  @bindable resourceClass: string;
  @bindable columnMetadata: Metadata[];
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
  @bindable eventTarget: any;
  @bindable localStoragePrefix: string;
  @bindable resourceKinds: ResourceKind[];
  @bindable filters: ResourcesListFilters = new ResourcesListFilters();
  @bindable resources: PageResult<Resource>;
  @bindable displayProgressBar: boolean;
  @bindable hideTopPagination: boolean;

  @bindable filtersChanged: (value: {
    target: any,
    filters: ResourcesListFilters
  }) => any = () => {
  };

  private sortByKey: string;
  private resultsPerPageKey: string;

  private subscriptions: Subscription[] = [];

  constructor(private alert: Alert,
              private i18n: I18N,
              private contextResourceClass: ContextResourceClass,
              private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private metadataRepository: MetadataRepository,
              private eventAggregator: EventAggregator) {
  }

  bind() {
    this.sortByKey = this.SORT_BY_KEY_PREFIX + (this.localStoragePrefix || '');
    this.resultsPerPageKey = this.RESULTS_PER_PAGE_KEY_PREFIX + (this.localStoragePrefix || '');
    this.subscribeToFilterChange('sortButtonToggled', (sort: ResourceSort) => this.sortButtonToggled(sort));
    this.subscribeToFilterChange('metadataFilterValueChanged',
      (filterChange: MetadataFilterChange) => this.metadataFilterValueChanged(filterChange));
    this.subscribeToFilterChange('placeFilterValueChanged', (placeIds: string[]) => this.placeFilterValueChanged(placeIds));
    this.subscribeToFilterChange('currentPageNumberChanged', (page: number) => this.currentPageNumberChanged(page));
    this.subscribeToFilterChange('elementsPerPageChanged', (resultsPerPage: number) => this.resultsPerPageChanged(resultsPerPage));
  }

  unbind() {
    this.subscriptions.forEach(subscription => subscription.dispose());
  }

  private notifyFiltersChanged = debounce(() => {
    this.filtersChanged({target: this.eventTarget, filters: this.filters});
  }, 100);

  private subscribeToFilterChange<T>(eventName: string, handler: (eventValue: T) => void) {
    this.subscriptions.push(this.eventAggregator.subscribe(eventName, (event: FilterChangedEvent<T>) => {
      if (event.target === this.eventTarget) {
        handler(event.value);
      }
    }));
  }

  private sortButtonToggled(resourceSort: ResourceSort) {
    this.filters.sortBy = resourceSort ? [resourceSort] : this.DEFAULT_SORTING;
    this.notifyFiltersChanged();
    LocalStorage.set(this.sortByKey, this.filters.sortBy);
  }

  private metadataFilterValueChanged(metadataIdWithValue: MetadataFilterChange) {
    if (!this.filters.contents) {
      this.filters.contents = {};
    }
    this.filters.contents[metadataIdWithValue.metadataId] = metadataIdWithValue.value;
    this.notifyFiltersChanged();
  }

  private placeFilterValueChanged(placesIds: string[]) {
    if (!this.filters.places) {
      this.filters.places = [];
    }
    this.filters.places = placesIds;
    this.notifyFiltersChanged();
  }

  private resultsPerPageChanged(newValue: number) {
    LocalStorage.set(this.resultsPerPageKey, newValue);
    this.filters.resultsPerPage = newValue;
    this.notifyFiltersChanged();
  }

  private currentPageNumberChanged(newValue: number) {
    this.filters.currentPage = newValue;
    this.notifyFiltersChanged();
  }
}
