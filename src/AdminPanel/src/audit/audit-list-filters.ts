import {observable} from "aurelia-binding";
import {AuditEntryListQuery} from "./audit-entry-list-query";
import {safeJsonParse} from "../common/utils/object-utils";

export class AuditListFilters {
  private static DEFAULT_PER_PAGE: number = 10;

  @observable resultsPerPage: number = 10;
  @observable currentPageNumber: number = 1;
  commandNames: string[] = [];
  resourceContents: NumberMap<string>;
  customColumns: { displayStrategy: string }[] = [];
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
    if (this.customColumns.length) {
      params.customColumns = JSON.stringify(this.customColumns.map(col => col.displayStrategy));
    }
    if (this.resourceContents) {
      params.resourceContents = JSON.stringify(this.resourceContents);
    }
    return params;
  }

  buildQuery(query: AuditEntryListQuery): AuditEntryListQuery {
    return query
      .filterByResourceContents(this.resourceContents)
      .filterByCommandNames(this.commandNames)
      .setPage(this.currentPageNumber)
      .setResultsPerPage(this.resultsPerPage);
  }

  static fromParams(params: StringMap<any>): AuditListFilters {
    const filters = new AuditListFilters();
    filters.resultsPerPage = +params.perPage || AuditListFilters.DEFAULT_PER_PAGE;
    filters.currentPageNumber = +params.page || 1;
    filters.commandNames = (params.commandNames || '').split(',').filter(commandName => !!commandName.trim());
    filters.resourceContents = safeJsonParse(params.resourceContents);
    filters.setCustomColumns(params.customColumns);
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
