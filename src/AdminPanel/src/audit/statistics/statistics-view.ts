import {autoinject} from "aurelia-dependency-injection";
import {AuditEntryRepository} from "../audit-entry-repository";
import {bindable, ComponentAttached} from "aurelia-templating";
import {NavigationInstruction, Router} from "aurelia-router";
import {debounce} from "lodash";
import {StatisticsFilters} from "audit/statistics/statistics-filters";
import {StatisticsBucket} from "audit/statistics/statistics-bucket";
import {removeValue, unique} from "common/utils/array-utils";
import {computedFrom} from "aurelia-binding";
import {StatisticsQuery} from "audit/statistics-query";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class StatisticsView implements ComponentAttached {
  @bindable filters: StatisticsFilters = new StatisticsFilters();
  @bindable resourceId: number;

  displayProgressBar = false;
  private error = '';
  private entries: StatisticsBucket[];
  private lastQuery: StatisticsQuery;
  private activated: boolean = false;

  constructor(private auditEntryRepository: AuditEntryRepository, private router: Router, private eventAggregator: EventAggregator) {
  }

  bind() {
    if (this.resourceId) {
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
    }
  }

  activate(params: any) {
    if (this.resourceId != undefined) {
      params.id = this.resourceId;
    }
    this.filters = StatisticsFilters.fromParams(params);
    this.fetchStatistics();
    this.activated = true;
  }

  attached() {
    if (!this.activated) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  fetchStatistics() {
    let route = this.resourceId ? 'resources/details' : 'audit';
    this.router.navigateToRoute(route, this.filters.toParams(), {trigger: false, replace: true});
    this.fetchEntries();
  }

  fetchEntries = debounce(() => {
    this.displayProgressBar = true;
    this.error = '';
    this.lastQuery = this.filters.buildQuery(this.auditEntryRepository.getStatisticsQuery());
    this.lastQuery
      .get()
      .then(entries => this.entries = entries)
      .catch(e => {
        this.entries = [];
        throw this.error = e;
      })
      .finally(() => this.displayProgressBar = false);
  }, 50);

  @computedFrom('entries')
  get eventGroups(): string[] {
    const eventGroups = this.entries ? unique(this.entries.map(e => e.eventGroup)) : [];
    if (eventGroups.includes('default')) {
      removeValue(eventGroups, 'default');
      eventGroups.push('default'); // push the "default" group to the end
    }
    return eventGroups;
  }

  @computedFrom('lastQuery')
  get currentParams(): string {
    return this.lastQuery ? $.param(this.lastQuery.getParams()) : '';
  }
}
