import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction, RoutableComponentActivate, Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {PageResult} from "resources/page-result";
import {AuditEntry} from "./audit-entry";
import {AuditEntryRepository} from "../audit-entry-repository";
import {AuditListFilters} from "./audit-list-filters";
import {AuditSettingsRepository} from "./filters/audit-settings-repository";
import {AuditSettings} from "./filters/audit-settings";
import {debounce} from "lodash";

@autoinject
export class Audit implements RoutableComponentActivate {
  @bindable filters: AuditListFilters = new AuditListFilters();
  @bindable resourceId: number;
  @bindable auditSettings: AuditSettings[] = [];
  private entries: PageResult<AuditEntry>;
  private displayProgressBar = false;
  private error = '';
  activated: boolean = false;
  exporting: boolean = false;
  exportSuccessText: boolean = false;

  constructor(private auditEntryRepository: AuditEntryRepository,
              private auditSettingsRepository: AuditSettingsRepository,
              private router: Router,
              private eventAggregator: EventAggregator) {
  }

  bind() {
    if (this.resourceId) {
      this.eventAggregator.subscribeOnce("router:navigation:success",
        (event: { instruction: NavigationInstruction }) => {
          this.activate(event.instruction.queryParams);
        });
    }
    this.auditSettings = this.auditSettingsRepository.getList();
  }

  activate(params: any) {
    delete params.id;
    if (this.resourceId != undefined) {
      params.id = this.resourceId;
    }
    this.filters = AuditListFilters.fromParams(params);
    this.filters.onChange = () => this.onAnyChange();
    this.onAnyChange();
    this.activated = true;
  }

  attached() {
    if (!this.activated) {
      this.activate(this.router.currentInstruction.queryParams);
    }
  }

  fetchEntries = debounce(() => {
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
  }, 50);

  onFiltersChanged() {
    this.filters.currentPageNumber = 1;
    this.onAnyChange();
  }

  onAnyChange() {
    let route = this.resourceId ? 'resources/details' : 'audit';
    this.router.navigateToRoute(route, this.filters.toParams(), {trigger: false, replace: true});
    this.fetchEntries();
  }

  exportToCSV() {
    this.exporting = true;
    this.filters.buildQuery(this.auditEntryRepository.getListQuery()).exportAuditToCsv().finally(() => {
      this.exporting = false;
      this.exportSuccessText = true;
      setTimeout(() => this.exportSuccessText = false, 3000);
    });
  }
}
