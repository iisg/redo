import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {ResourceSort, SortDirection} from "../../resource-sort";
import {Router} from "aurelia-router";

@autoinject
export class SortButton {
  @bindable columnId: number | string;
  @bindable sortBy: ResourceSort[];
  sortDirection: SortDirection;

  constructor(private router: Router) {
  }

  sortByChanged() {
    const columnSort = this.columnSort();
    this.sortDirection = columnSort ? columnSort.direction : SortDirection.NONE;
  }

  fetchSortedResources() {
    const columnSort = this.columnSort();
    if (!columnSort) {
      if (!this.sortBy) {
        this.sortBy = [];
      }
      this.sortBy.push(new ResourceSort(this.columnId, SortDirection.ASC));
    } else if (columnSort.direction === SortDirection.ASC) {
      columnSort.direction = SortDirection.DESC;
    } else {
      if (columnSort.columnId === 'id') {
        columnSort.direction = SortDirection.ASC;
      } else {
        this.sortBy.splice(this.sortBy.indexOf(columnSort), 1);
      }
    }
    const currentInstruction = this.router.currentInstruction;
    let parameters = Object.assign(currentInstruction.params, currentInstruction.queryParams);
    parameters['sortBy'] = this.sortBy.length ? JSON.stringify(this.sortBy) : undefined;
    this.router.navigateToRoute(currentInstruction.config.name, parameters, {replace: true});
  }

  columnSort(): ResourceSort {
    return this.sortBy && this.sortBy.find(resourceMetadataSort => resourceMetadataSort.columnId === this.columnId);
  }
}
