import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {ResourceSort, SortDirection} from "../../../resources/resource-sort";

@autoinject
export class SortButton {
  @bindable columnId: number | string;
  @bindable sortBy: ResourceSort[];
  resourceSort: ResourceSort;

  constructor(private eventAggregator: EventAggregator, private i18n: I18N) {
  }

  bind() {
    this.sortByChanged();
  }

  sortByChanged() {
    this.resourceSort = this.sortBy && this.sortBy.find(resourceSort => resourceSort.columnId == this.columnId);
  }

  toggle() {
    if (this.resourceSort) {
      if (this.resourceSort.direction == SortDirection.ASC) {
        this.resourceSort.direction = SortDirection.DESC;
      } else {
        this.resourceSort.direction = SortDirection.ASC;
      }
    } else {
      this.resourceSort = new ResourceSort(this.columnId, SortDirection.ASC, this.i18n.getLocale().toUpperCase());
    }
    this.eventAggregator.publish('sortButtonToggled', this.resourceSort);
  }
}
