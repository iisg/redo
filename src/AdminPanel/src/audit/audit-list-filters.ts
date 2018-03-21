import {observable} from "aurelia-binding";
import {AuditEntryListQuery} from "./audit-entry-list-query";

export class AuditListFilters {
  private static DEFAULT_PER_PAGE: number = 10;

  @observable resultsPerPage: number = 10;
  @observable currentPageNumber: number = 1;
  commandNames: string[] = [];
  onChange: VoidFunction = () => undefined;

  toParams(): StringMap<any> {
    const params: StringMap<any> = {};
    if (this.resultsPerPage != AuditListFilters.DEFAULT_PER_PAGE) {
      params.perPage = this.resultsPerPage;
    }
    if (this.currentPageNumber != 1) {
      params.page = this.currentPageNumber;
    }
    if (this.commandNames.length) {
      params.commandNames = this.commandNames.join(',');
    }
    return params;
  }

  buildQuery(query: AuditEntryListQuery): AuditEntryListQuery {
    return query
      .filterByCommandNames(this.commandNames)
      .setPage(this.currentPageNumber)
      .setResultsPerPage(this.resultsPerPage);
  }

  static fromParams(params: StringMap<any>): AuditListFilters {
    const filters = new AuditListFilters();
    filters.resultsPerPage = +params.perPage || AuditListFilters.DEFAULT_PER_PAGE;
    filters.currentPageNumber = +params.page || 1;
    filters.commandNames = (params.commandNames || '').split(',').filter(commandName => !!commandName.trim());
    return filters;
  }

  private callOnChange() {
    if (this.onChange) {
      this.onChange();
    }
  }

  currentPageNumberChanged() {
    this.callOnChange();
  }

  resultsPerPageChanged() {
    this.callOnChange();
  }
}
