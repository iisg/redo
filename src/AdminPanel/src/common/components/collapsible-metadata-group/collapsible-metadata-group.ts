import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {LocalStorage} from "common/utils/local-storage";

@autoinject
export class CollapsibleMetadataGroup {
  private readonly COLLAPSED_METADATA_GROUPS_IDS_KEY_SUFFIX = "collapsedMetadataGroupsIds";

  @bindable resourceKindId: number;
  @bindable metadataGroupId: string;
  @bindable disabled: boolean;
  private collapsedMetadataGroupsIdsKey: string;
  private collapsedMetadataGroupsIds: string[];

  bind() {
    this.collapsedMetadataGroupsIdsKey = this.resourceKindId + '-' + this.COLLAPSED_METADATA_GROUPS_IDS_KEY_SUFFIX;
    this.collapsedMetadataGroupsIds = LocalStorage.get(this.collapsedMetadataGroupsIdsKey);
    if (!this.collapsedMetadataGroupsIds) {
      this.collapsedMetadataGroupsIds = [];
    }
  }

  toggleMetadataGroupVisibility() {
    let collapsedMetadataGroupsIds = LocalStorage.get(this.collapsedMetadataGroupsIdsKey);
    if (!collapsedMetadataGroupsIds) {
      collapsedMetadataGroupsIds = this.collapsedMetadataGroupsIds;
    }
    if (this.collapsed) {
      const indexOfCollapsedMetadataGroupsIds = collapsedMetadataGroupsIds.indexOf(this.metadataGroupId);
      if (indexOfCollapsedMetadataGroupsIds != -1) {
        collapsedMetadataGroupsIds.splice(indexOfCollapsedMetadataGroupsIds, 1);
      }
    } else {
      if (!collapsedMetadataGroupsIds.includes(this.metadataGroupId)) {
        collapsedMetadataGroupsIds.push(this.metadataGroupId);
      }
    }
    this.collapsedMetadataGroupsIds = collapsedMetadataGroupsIds;
    LocalStorage.set(this.collapsedMetadataGroupsIdsKey, this.collapsedMetadataGroupsIds);
  }

  @computedFrom('collapsedMetadataGroupsIds', 'collapsedMetadataGroupsIds.length')
  get collapsed(): boolean {
    return this.collapsedMetadataGroupsIds.includes(this.metadataGroupId);
  }
}
