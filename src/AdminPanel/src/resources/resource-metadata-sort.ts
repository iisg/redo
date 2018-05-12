import {SortDirection} from "./list/resources-list-table";

export class ResourceMetadataSort {
  readonly metadataId: number;
  direction: SortDirection;

  constructor(metadataId: number, direction: SortDirection) {
    this.metadataId = metadataId;
    this.direction = direction;
  }
}
