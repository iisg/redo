import {ResourceSort} from "../resource-sort";

export class ResourcesListFilters {
  contents: NumberMap<string> = {};
  places: string[] = [];
  sortBy: ResourceSort[] = [];
  resultsPerPage: number = 10;
  currentPage: number = 1;
  kindIds: number[] = [];
}

export interface FilterChangedEvent<Value> {
  value: Value;
  target?: string;
}
