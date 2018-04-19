import {AuditEntryRepository} from "./audit-entry-repository";
import {autoinject} from "aurelia-dependency-injection";
import {RoutableComponentActivate, Router} from "aurelia-router";
import {AuditEntry} from "./audit-entry";
import {PageResult} from "../resources/page-result";
import {AuditListFilters} from "./audit-list-filters";

@autoinject
export class AuditPage implements RoutableComponentActivate {
  private entries: PageResult<AuditEntry>;
  private displayProgressBar = false;
  private filters: AuditListFilters;
  private error: '';

  constructor(private auditEntryRepository: AuditEntryRepository, private router: Router) {
  }

  activate(params: any) {
    this.filters = AuditListFilters.fromParams(params);
    this.filters.onChange = () => this.onFiltersChanged();
    this.fetchEntries();
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
    this.router.navigateToRoute('audit', this.filters.toParams(), {replace: true});
  }
}
