import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {getQueryParameters} from "common/utils/url-utils";
import {ResourceSort, SortDirection} from "../../../resources/resource-sort";

@autoinject
export class SortButton {
  @bindable columnId: number | string;
  @bindable sortBy: ResourceSort[];
  sortDirection: SortDirection;

  constructor(private eventAggregator: EventAggregator, private router: Router, private i18n: I18N) {
  }

  sortByChanged() {
    const columnSort = this.columnSort();
    this.sortDirection = columnSort ? columnSort.direction : SortDirection.NONE;
  }

  updateURL() {
    const columnSort = this.columnSort();
    if (!columnSort) {
      this.sortBy = [];
      const language = this.i18n.getLocale().toUpperCase();
      this.sortBy.push(new ResourceSort(this.columnId, SortDirection.ASC, language));
    } else if (columnSort.direction === SortDirection.ASC) {
      columnSort.direction = SortDirection.DESC;
    } else {
      if (columnSort.columnId === 'id') {
        columnSort.direction = SortDirection.ASC;
      } else {
        this.sortBy = [];
      }
    }
    const currentInstruction = this.router.currentInstruction;
    let parameters = Object.assign(currentInstruction.params, getQueryParameters());
    parameters['sortBy'] = this.sortBy.length ? JSON.stringify(this.sortBy) : undefined;
    this.router.navigateToRoute(currentInstruction.config.name, parameters, {replace: true});
    this.eventAggregator.publish('sortButtonToggled', parameters);
  }

  columnSort(): ResourceSort {
    return this.sortBy && this.sortBy.find(resourceMetadataSort => resourceMetadataSort.columnId === this.columnId);
  }
}
