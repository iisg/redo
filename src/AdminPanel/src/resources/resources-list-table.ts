import {bindable} from "aurelia-templating";
import {Resource} from "./resource";
import {oneTime, twoWay} from "common/components/binding-mode";
import {Metadata} from "../resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceMetadataSort} from "./resource-metadata-sort";

@autoinject
export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable columnMetadata: Metadata[];
  @bindable resourceClass: string;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
  @bindable(twoWay) sortBy: ResourceMetadataSort[];
  @bindable sortable: boolean = true;
}

export enum SortDirection {
  ASC = 'ASC',
  DESC = 'DESC',
  NONE = '',
}
