import {bindable} from "aurelia-templating";
import {Metadata} from "../../resources-config/metadata/metadata";
import {inArray} from "../../common/utils/array-utils";
import {computedFrom} from "aurelia-binding";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class RequiredMetadataList {
  @bindable resourceClass: string;
  @bindable requiredMetadataIds: number[];

  allMetadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  resourceClassChanged() {
    this.fetchMetadataList();
  }

  private async fetchMetadataList() {
    this.allMetadataList = await this.metadataRepository.getListByClass(this.resourceClass);
  }

  @computedFrom('requiredMetadataIds')
  get requiredMetadataList(): Metadata[] {
    if (this.allMetadataList) {
      return this.allMetadataList.filter(metadata => inArray(metadata.id, this.requiredMetadataIds));
    }
    return [];
  }
}
