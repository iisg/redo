import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {oneTime, twoWay} from "../../common/components/binding-mode";
import {Metadata, filterableControls} from "../../resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceMetadataSort} from "../resource-metadata-sort";
import {inArray} from "../../common/utils/array-utils";

@autoinject
export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable columnMetadata: Metadata[];
  @bindable resourceClass: string;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
  @bindable(twoWay) sortBy: ResourceMetadataSort[];
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  @bindable sortable: boolean = true;

  isFilterableMetadata(metadata: Metadata) {
    return inArray(metadata.control, filterableControls);
  }
}

export enum SortDirection {
  ASC = 'ASC',
  DESC = 'DESC',
  NONE = '',
}
