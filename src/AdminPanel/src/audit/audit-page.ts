import {AuditEntryRepository} from "./audit-entry-repository";
import {autoinject} from "aurelia-dependency-injection";
import {NavigationInstruction, RoutableComponentActivate, Router} from "aurelia-router";
import {AuditEntry} from "./audit-entry";
import {PageResult} from "../resources/page-result";
import {AuditListFilters} from "./audit-list-filters";
import {EventAggregator} from "aurelia-event-aggregator";
import {bindable} from "aurelia-templating";

@autoinject
export class AuditPage implements RoutableComponentActivate {
  private entries: PageResult<AuditEntry>;
  private displayProgressBar = false;
  @bindable filters: AuditListFilters;
  private error: '';
  @bindable resourceId: number;
  activated: boolean = false;

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
    this.filters = AuditListFilters.fromParams(params);
    this.filters.onChange = () => this.onAnyChange();
    this.fetchEntries();
    this.activated = true;
  }

  attached() {
    if (!this.activated) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  fetchEntries() {
    this.displayProgressBar = true;
    this.error = '';
    this.filters
      .buildQuery(this.auditEntryRepository.getListQuery())
      .suppressError()
      .get()
      .then(entries => this.entries = entries)
      .catch(e => {
        this.entries = new PageResult<AuditEntry>();
        throw this.error = e;
      })
      .finally(() => this.displayProgressBar = false);
  }

  onFiltersChanged() {
    this.filters.currentPageNumber = 1;
    this.onAnyChange();
  }

  onAnyChange() {
    let route = this.resourceId ? 'resources/details' : 'audit';
    this.router.navigateToRoute(route, this.filters.toParams(), {trigger: false, replace: true});
    this.fetchEntries();
  }
}
