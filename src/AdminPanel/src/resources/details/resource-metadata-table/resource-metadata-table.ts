import {bindable, ComponentAttached} from "aurelia-templating";
import {Resource} from "../../resource";
import {GroupMetadataList, Metadata} from "resources-config/metadata/metadata";
import {booleanAttribute} from "common/components/boolean-attribute";
import {inArray} from "common/utils/array-utils";
import {groupMetadata} from "../../../common/utils/metadata-utils";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataGroupRepository} from "../../../resources-config/metadata/metadata-group-repository";
import {_} from 'lodash';

@autoinject
export class ResourceMetadataTable implements ComponentAttached {
  @bindable resource: Resource;
  @bindable resources: Resource[];
  @bindable metadataList: Metadata[];
  @bindable @booleanAttribute hideEmptyMetadata: boolean = false;
  @bindable @booleanAttribute hideUnchangedMetadata: boolean = false;
  @bindable @booleanAttribute hidePlaceInformation: boolean = false;
  @bindable @booleanAttribute showResourceId: boolean = false;
  @bindable @booleanAttribute showResourceKind: boolean = false;
  @bindable @booleanAttribute briefOnly: boolean = false;
  @bindable @booleanAttribute hideMetadataGroups: boolean = false;
  @bindable checkMetadataBrief: boolean = false;
  metadataGroups: GroupMetadataList[];

  constructor(private metadataGroupRepository: MetadataGroupRepository) {
  }

  metadataListChanged() {
    const emptyMetadata = this.metadataList.filter(metadata => {
      const values = this.resources.filter(resource => {
        return resource.contents[metadata.id];
      });
      return values.length == 0;
    });
    if (emptyMetadata.length > 0 && this.hideEmptyMetadata) {
      this.metadataList = this.metadataList.filter(metadata => !inArray(metadata, emptyMetadata));
    }
    if (this.resources.length > 1) {
      const unchangedMetadata = this.metadataList.filter(metadata => {
        const values = this.resources.map(resource => {
          return resource.contents[metadata.id];
        });
        return values.every((val, i, arr) => _.isEqual(val, values[0]));
      });
      if (unchangedMetadata.length > 0 && this.hideUnchangedMetadata) {
        this.metadataList = this.metadataList.filter(metadata => !inArray(metadata, unchangedMetadata));
      }
    }
    const briefMetadata = this.metadataList.filter(metadata => metadata.shownInBrief);
    if (this.briefOnly && briefMetadata.length < this.metadataList.length) {
      this.metadataList = briefMetadata;
    }
    this.metadataGroups = groupMetadata(this.metadataList, this.metadataGroupRepository.getIds());
  }

  attached(): void {
    if (this.resource) {
      this.resources = [this.resource];
    }
    if (!this.metadataList) {
      this.metadataList = this.buildMetadataList(this.resources);
    }
  }

  private buildMetadataList(resources: Resource[]) {
    let newMetadataList = [];
    let metadataListIds = [];
    let resourceKindIds = [];
    resources.forEach(resource => {
      if (!inArray(resource.kind.id, resourceKindIds)) {
        resource.kind.metadataList.forEach(metadata => {
          if (!inArray(metadata.id, metadataListIds)) {
            metadataListIds.push(metadata.id);
            newMetadataList.push(metadata);
          }
        });
        resourceKindIds.push(resource.kind.id);
      }
    });
    return newMetadataList;
  }
}
