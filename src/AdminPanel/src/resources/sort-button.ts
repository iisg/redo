import {Metadata} from "../resources-config/metadata/metadata";
import {ResourceMetadataSort} from "./resource-metadata-sort";
import {SortDirection} from "./resources-list-table";
import {twoWay} from "../common/components/binding-mode";
import {bindable} from "aurelia-templating";
import {Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class SortButton {
  @bindable metadata: Metadata;
  @bindable(twoWay) sortBy: ResourceMetadataSort[];
  sortDirection: SortDirection;

  constructor(private router: Router) {
  }

  sortByChanged() {
    const metadataSort = this.metadataSort();
    this.sortDirection = metadataSort ? metadataSort.direction : SortDirection.NONE;
  }

  fetchSortedResources() {
    const metadataSort = this.metadataSort();
    if (!metadataSort) {
      if (!this.sortBy) {
        this.sortBy = [];
      }
      this.sortBy.push(new ResourceMetadataSort(this.metadata.id, SortDirection.ASC));
    } else if (metadataSort.direction === SortDirection.ASC) {
      metadataSort.direction = SortDirection.DESC;
    } else {
      this.sortBy.splice(this.sortBy.indexOf(metadataSort), 1);
    }
    const currentInstruction = this.router.currentInstruction;
    let parameters = Object.assign(currentInstruction.params, currentInstruction.queryParams);
    parameters['sortBy'] = this.sortBy.length ? JSON.stringify(this.sortBy) : undefined;
    this.router.navigateToRoute(currentInstruction.config.name, parameters, {replace: true});
  }

  metadataSort(): ResourceMetadataSort {
    return this.sortBy && this.sortBy.find(resourceMetadataSort => resourceMetadataSort.metadataId === this.metadata.id);
  }
}
