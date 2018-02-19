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

  constructor(private router: Router) {
  }

  get sortDirection(): SortDirection {
    const metadataSort = this.sortBy.find(v => v.metadataId === this.metadata.id);
    return metadataSort ? metadataSort.direction : SortDirection.NONE;
  }

  fetchSortedResources() {
    const direction = this.sortDirection;
    const metadataSort = this.sortBy.find(v => v.metadataId === this.metadata.id);
    if (!direction) {
      this.sortBy.push(new ResourceMetadataSort(this.metadata.id, SortDirection.ASC));
    } else if (direction === SortDirection.ASC) {
      metadataSort.direction = SortDirection.DESC;
    } else {
      const index = this.sortBy.indexOf(metadataSort);
      this.sortBy.splice(index, 1);
    }
    const queryParams = this.router.currentInstruction.queryParams;
    queryParams['resourceClass'] = this.metadata.resourceClass;
    queryParams['sortBy'] = JSON.stringify(this.sortBy);
    this.router.navigateToRoute('resources', queryParams, {replace: true});
  }
}
