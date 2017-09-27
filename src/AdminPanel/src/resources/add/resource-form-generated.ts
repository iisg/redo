import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Metadata} from "resources-config/metadata/metadata";
import {twoWay} from "common/components/binding-mode";
import {flatten, inArray} from "common/utils/array-utils";
import {keysByValue} from "common/utils/object-utils";
import {RequirementState} from "workflows/workflow";

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
    const currentPlaces = this.resource.currentPlaces || [];
    this.requiredMetadataIds = flatten(currentPlaces.map(place => keysByValue(place.restrictingMetadataIds, RequirementState.REQUIRED)));
    this.lockedMetadataIds = flatten(currentPlaces.map(place => keysByValue(place.restrictingMetadataIds, RequirementState.LOCKED)));
  }

  editingDisabledForMetadata(metadata: Metadata): boolean {
    const isParent = metadata.baseId == SystemMetadata.PARENT.baseId;
    return (isParent && this.disableParent) || this.metadataIsLocked(metadata);
  }

  metadataIsRequired(metadata: Metadata): boolean {
    return inArray(metadata.baseId, this.requiredMetadataIds);
  }

  metadataIsLocked(metadata: Metadata): boolean {
    return inArray(metadata.baseId, this.lockedMetadataIds);
  }

  metadataDeterminesAssignee(metadata: Metadata): boolean {
    return this.resource.transitionAssigneeMetadata[metadata.baseId] !== undefined
      && this.resource.transitionAssigneeMetadata[metadata.baseId].length > 0;
  }
}
