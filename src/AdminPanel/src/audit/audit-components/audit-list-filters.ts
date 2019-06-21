import {observable} from "aurelia-binding";
import {AuditEntryListQuery} from "../audit-entry-list-query";
import {safeJsonParse} from "common/utils/object-utils";
import {DateRangeConfig} from "./filters/date-range-config";

export class AuditListFilters {
  static DEFAULT_RESULTS_PER_PAGE: number = 10;

  @observable resultsPerPage: number = 10;
  @observable currentPageNumber: number = 1;
  commandNames: string[] = [];
  dateFrom: string;
  dateTo: string;
  users: string[] = [];
  resourceKinds: string[] = [];
  transitions: string[] = [];
  resourceContents: NumberMap<string>;
  customColumns: { displayStrategy: string }[] = [];
  resourceId: number;
  onChange: VoidFunction = () => undefined;

  toParams(): StringMap<any> {
    const params: StringMap<any> = {};
    if (this.resultsPerPage != AuditListFilters.DEFAULT_RESULTS_PER_PAGE) {
      params['resultsPerPage'] = this.resultsPerPage;
    }
    if (this.currentPageNumber != 1) {
      params['currentPageNumber'] = this.currentPageNumber;
    }
    if (this.commandNames.length) {
      params['commandNames'] = this.commandNames.join(',');
    }
    if (this.dateFrom) {
      params['dateFrom'] = this.dateFrom;
    }
    if (this.dateTo) {
      params['dateTo'] = this.dateTo;
    }
    if (this.users.length) {
      params['users'] = this.users.join(',');
    }
    if (this.resourceKinds.length) {
      params['resourceKinds'] = this.resourceKinds.join(',');
    }
    if (this.transitions.length) {
      params['transitions'] = this.transitions.join(',');
    }
    if (this.customColumns.length) {
      params['customColumns'] = JSON.stringify(this.customColumns.map(col => col.displayStrategy));
    }
    if (this.resourceContents) {
      params['resourceContents'] = JSON.stringify(this.resourceContents);
    }
    if (this.resourceId) {
      params['id'] = this.resourceId;
    }
    params['tab'] = 'audit';
    return params;
  }

  buildQuery(query: AuditEntryListQuery): AuditEntryListQuery {
    if (this.resourceId) {
      query = query.filterByResourceId(this.resourceId);
    }
    if (this.dateFrom) {
      query = query.filterByDateFrom(this.dateFrom);
    }
    if (this.dateTo) {
      query = query.filterByDateTo(this.dateTo);
    }
    return query
      .filterByResourceContents(this.resourceContents)
      .filterByCommandNames(this.commandNames)
      .filterByUsers(this.users)
      .filterByResourceKinds(this.resourceKinds)
      .filterByTransitions(this.transitions)
      .addCustomColumns(this.customColumns.map(({displayStrategy}) => displayStrategy))
      .setCurrentPageNumber(this.currentPageNumber)
      .setResultsPerPage(this.resultsPerPage);
  }

  static fromParams(params: StringMap<any>, filters: AuditListFilters = undefined): AuditListFilters {
    if (!filters) {
      filters = new AuditListFilters();
    }
    filters.resultsPerPage = +params['resultsPerPage'] || AuditListFilters.DEFAULT_RESULTS_PER_PAGE;
    filters.currentPageNumber = +params['currentPageNumber'] || 1;
    filters.commandNames = (params['commandNames'] || '').split(',').filter(commandName => !!commandName.trim());
    filters.dateFrom = DateRangeConfig.isDateValid(params['dateFrom']) ? params['dateFrom'] : "";
    filters.dateTo = DateRangeConfig.isDateValid(params['dateTo']) ? params['dateTo'] : "";
    filters.users = (params['users'] || '').split(',').filter(user => !!user.trim());
    filters.resourceKinds = (params['resourceKinds'] || '').split(',').filter(resourceKind => !!resourceKind.trim());
    filters.transitions = (params['transitions'] || '').split(',').filter(transition => !!transition.trim());
    filters.resourceContents = safeJsonParse(params['resourceContents']);
    filters.setCustomColumns(params['customColumns']);
    filters.resourceId = +params['id'];
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

  addNewCustomColumn() {
    this.customColumns.push({displayStrategy: ''});
  }

  removeCustomColumn(column) {
    const columnIndex = this.customColumns.indexOf(column);
    if (columnIndex >= 0) {
      this.customColumns.splice(columnIndex, 1);
    }
  }

  private setCustomColumns(serializedCustomColumns: string): void {
    const customColumns = safeJsonParse(serializedCustomColumns);
    if (Array.isArray(customColumns)) {
      this.customColumns = customColumns.map(col => {
        return {displayStrategy: col};
      });
    }
  }
}
