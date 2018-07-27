import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "../resource";
import {Metadata} from "../../resources-config/metadata/metadata";
import {oneTime, twoWay} from "../../common/components/binding-mode";
import {ResourceSort} from "../resource-sort";
import {inArray} from "../../common/utils/array-utils";
import {filterableControls} from "../../resources-config/metadata/metadata-control";

@autoinject
export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable columnMetadata: Metadata[];
  @bindable resourceClass: string;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  @bindable sortBy: ResourceSort[];
  @bindable sortable: boolean = true;
  @bindable filterable: boolean = true;

  isFilterableMetadata(metadata: Metadata) {
    return inArray(metadata.control, filterableControls);
  }
}
