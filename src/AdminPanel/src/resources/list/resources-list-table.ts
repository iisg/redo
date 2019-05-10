import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "../resource";
import {Metadata} from "resources-config/metadata/metadata";
import {oneTime, twoWay} from "common/components/binding-mode";
import {ResourceSort} from "../resource-sort";
import {inArray} from "common/utils/array-utils";
import {filterableControls} from "resources-config/metadata/metadata-control";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {getMergedBriefMetadata} from "../../common/utils/metadata-utils";
import {computedFrom} from "aurelia-binding";

@autoinject
export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable disabledMetadata: Metadata;
  @bindable resourceClass: string;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
  @bindable(twoWay) contentsFilter: NumberMap<string>;
  @bindable placesFilter: string[];
  @bindable sortBy: ResourceSort[];
  @bindable resourceKinds: ResourceKind[];
  @bindable eventTarget: any;

  isFilterableMetadata(metadata: Metadata) {
    return inArray(metadata.control, filterableControls);
  }

  @computedFrom("resources")
  get briefMetadata(): Metadata[] {
    const resourceKindsOnTheList = [];
    if (this.resources) {
      this.resources.forEach(resource => {
        if (!resourceKindsOnTheList.includes(resource.kind)) {
          resourceKindsOnTheList.push(resource.kind);
        }
      });
    }
    return getMergedBriefMetadata(resourceKindsOnTheList);
  }
}
