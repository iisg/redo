import {bindable, ComponentAttached} from "aurelia-templating";
import {Resource} from "../../resource";
import {Metadata} from "resources-config/metadata/metadata";
import {booleanAttribute} from "common/components/boolean-attribute";
import {inArray} from "common/utils/array-utils";
import {groupMetadata} from "../../../common/utils/metadata-utils";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataGroupRepository} from "../../../resources-config/metadata/metadata-group-repository";

@autoinject
export class ResourceMetadataTable implements ComponentAttached {
  @bindable resource: Resource;
  @bindable metadataList: Metadata[];
  @bindable @booleanAttribute hideEmptyMetadata: boolean = false;
  @bindable @booleanAttribute hidePlaceInformation: boolean = false;
  @bindable @booleanAttribute showResourceKind: boolean = false;
  @bindable @booleanAttribute briefOnly: boolean = false;
  @bindable @booleanAttribute hideMetadataGroups: boolean = false;
  metadataGroups: { groupId, metadataList: Metadata[] }[];

  constructor(private metadataGroupRepository: MetadataGroupRepository) {
  }

  metadataListChanged() {
    const emptyMetadata = this.metadataList.filter(metadata => {
      const values = this.resource.contents[metadata.id];
      return values == undefined || values.length == 0;
    });
    if (emptyMetadata.length > 0 && this.hideEmptyMetadata) {
      this.metadataList = this.metadataList.filter(metadata => !inArray(metadata, emptyMetadata));
    }
    const briefMetadata = this.metadataList.filter(metadata => metadata.shownInBrief);
    if (this.briefOnly && briefMetadata.length < this.metadataList.length) {
      this.metadataList = briefMetadata;
    }
    this.metadataGroups = groupMetadata(this.metadataList, this.metadataGroupRepository.getIds());
  }

  attached(): void {
    if (this.metadataList == undefined) {
      this.metadataList = this.resource.kind.metadataList;
    }
  }
}
