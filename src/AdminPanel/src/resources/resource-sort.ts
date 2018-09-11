export class ResourceSort {
  readonly columnId: number | string;
  direction: SortDirection;
  language: string;

  constructor(metadataId: number | string, direction: SortDirection, language: string) {
    this.columnId = metadataId;
    this.direction = direction;
    this.language = language;
  }
}

export enum SortDirection {
  ASC = 'ASC',
  DESC = 'DESC',
  NONE = '',
}
