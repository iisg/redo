import {AuditEntryRepository} from "./audit-entry-repository";
import {autoinject} from "aurelia-dependency-injection";
import {RoutableComponentActivate, Router} from "aurelia-router";
import {AuditEntry} from "./audit-entry";
import {PageResult} from "../resources/page-result";
import {AuditListFilters} from "./audit-list-filters";

@autoinject
export class AuditPage implements RoutableComponentActivate {
  private entries: PageResult<AuditEntry>;

  private filters: AuditListFilters;

  constructor(private auditEntryRepository: AuditEntryRepository, private router: Router) {
  }

  activate(params: any) {
    this.filters = AuditListFilters.fromParams(params);
    this.filters.onChange = () => this.onFiltersChanged();
    this.fetchEntries();
  }

  async fetchEntries() {
    this.entries = await this.filters.buildQuery(this.auditEntryRepository.getListQuery()).get();
  }

  onFiltersChanged() {
    this.router.navigateToRoute('audit', this.filters.toParams(), {replace: true});
  }
}
