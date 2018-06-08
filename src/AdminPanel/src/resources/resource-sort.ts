export class ResourceSort {
  readonly columnId: number | string;
  direction: SortDirection;

  constructor(metadataId: number | string, direction: SortDirection) {
    this.columnId = metadataId;
    this.direction = direction;
  }
}

export enum SortDirection {
  ASC = 'ASC',
  DESC = 'DESC',
  NONE = '',
}
