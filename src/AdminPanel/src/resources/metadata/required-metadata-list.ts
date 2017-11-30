import {bindable} from "aurelia-templating";
import {Metadata} from "../../resources-config/metadata/metadata";
import {inArray} from "../../common/utils/array-utils";
import {computedFrom} from "aurelia-binding";

export class RequiredMetadataList {
  @bindable allMetadataList: Metadata[];
  @bindable requiredMetadataIds: number[];

  @computedFrom('metadataList', 'requiredMetadataIds')
  get requiredMetadataList(): Metadata[] {
    return this.allMetadataList.filter(metadata => inArray(metadata.baseId, this.requiredMetadataIds));
  }
}
