import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Metadata} from "resources-config/metadata/metadata";
import {twoWay} from "common/components/binding-mode";
import {flatten} from "common/utils/array-utils";

@autoinject
export class ResourceFormGenerated {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) resource: Resource;
  @bindable disableParent: boolean = false;

  currentLanguageCode: string;
  lockedMetadataIds: number[];
  requiredMetadataIds: number[];

  constructor(i18n: I18N) {
    this.currentLanguageCode = i18n.getLocale().toUpperCase();
  }

  resourceKindChanged() {
    if (!this.resourceKind) {
      this.resource.contents = {};
    }
  }

  resourceChanged() {
    const currentPlaces = this.resource.currentPlaces !== undefined
      ? Object.keys(this.resource.currentPlaces).map(key => this.resource.currentPlaces[key])
      : [];
    this.requiredMetadataIds = flatten(currentPlaces.map(place => place.requiredMetadataIds));
    this.lockedMetadataIds = flatten(currentPlaces.map(place => place.lockedMetadataIds));
  }

  editingDisabledForMetadata(metadata: Metadata): boolean {
    const isParent = metadata.baseId == SystemMetadata.PARENT.baseId;
    return (isParent && this.disableParent) || this.metadataIsLocked(metadata);
  }

  metadataIsRequired(metadata: Metadata) {
    return this.requiredMetadataIds.indexOf(metadata.baseId) != -1;
  }

  metadataIsLocked(metadata: Metadata) {
    return this.lockedMetadataIds.indexOf(metadata.baseId) != -1;
  }
}
