import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {Metadata} from "resources-config/metadata/metadata";
import {diff, flatten, inArray} from "common/utils/array-utils";
import {numberKeysByValue} from "common/utils/object-utils";
import {RequirementState} from "workflows/workflow";
import {computedFrom} from "aurelia-binding";
import {AllMetadataValueValidator} from "common/validation/rules/all-metadata-value-validator";
import {ValidationController} from "aurelia-validation";
import {EntitySerializer} from "../../common/dto/entity-serializer";
import {BindingSignaler} from "aurelia-templating-resources";
import {MetadataValue} from "../metadata-value";
import {WorkflowPlace} from "./../../workflows/workflow";
import {debounce} from "lodash";

@autoinject
export class ResourceFormGenerated {
  @bindable resourceKind: ResourceKind;
  @bindable resource: Resource;
  @bindable parent: Resource;
  @bindable requiredMetadataIdsForTransition: number[];
  @bindable validationController: ValidationController;
  @bindable targetPlaces: WorkflowPlace[];
  @bindable skipValidation: boolean = false;

  currentLanguageCode: string;
  lockedMetadataIds: number[];
  requiredMetadataIds: number[];
  removedValues: AnyMap<any[]> = {};

  /*
   contentsValidator is computed from resourceKind and requiredMetadataIdsForTransition - second one is bound later
   after resourceKind is changed view binds old contentsValidator
   @bindable solves this problem by binding current contentsValidator after computing new ones
   */
  @bindable contentsValidator: NumberMap<any>;

  constructor(i18n: I18N,
              private signaler: BindingSignaler,
              private allMetadataValidator: AllMetadataValueValidator,
              private entitySerializer: EntitySerializer) {
    this.currentLanguageCode = i18n.getLocale().toUpperCase();
  }

  @computedFrom('resourceKind', 'resourceKind.metadataList')
  get metadataList(): Metadata[] {
    if (this.resourceKind) {
      const metadataList = this.skipValidation
        ? this.resourceKind.metadataList
        : this.resourceKind.metadataList.filter(v => v.id != SystemMetadata.PARENT.id);
      return metadataList.filter(m => m.control != 'display-strategy');
    }
  }

  @computedFrom('parent')
  get disableParent(): boolean {
    return this.parent !== undefined;
  }

  resourceKindChanged() {
    if (!this.resource || !this.resource.contents) {
      return;
    }
    if (this.resourceKind) {
      this.setResourceContents();
      this.buildMetadataValidators();
    } else {
      this.resource.contents = {};
    }
    this.setParent();
  }

  requiredMetadataIdsForTransitionChanged() {
    this.buildMetadataValidators();
  }

  private buildMetadataValidators = debounce(() => {
    this.contentsValidator = {};
    if (!this.resourceKind) {
      return;
    }
    for (let metadata of this.resourceKind.metadataList) {
      const clonedMetadata = this.entitySerializer.clone(metadata);
      if (inArray(clonedMetadata.id, this.requiredMetadataIdsForTransition || [])) {
        clonedMetadata.constraints.minCount = 1;
      }
      this.contentsValidator[clonedMetadata.id] = this.allMetadataValidator.createRules(clonedMetadata).rules;
    }
    this.signaler.signal('metadata-validators-changed');
  }, 500);

  private setResourceContents() {
    const previousMetadata = Object.keys(this.resource.contents);
    const newMetadata = this.resourceKind.metadataList.map(metadata => metadata.id);
    const toBeRemoved = diff(previousMetadata, newMetadata);
    const toBeAdded = diff(newMetadata, previousMetadata);
    for (const metadataId of toBeRemoved) {
      this.removedValues[metadataId] = this.resource.contents[metadataId];
      delete this.resource.contents[metadataId];
    }
    for (const metadataId of toBeAdded) {
      if ((metadataId in this.removedValues) && this.removedValues[metadataId]) {
        this.resource.contents[metadataId] = this.removedValues[metadataId];
        delete this.removedValues[metadataId];
      } else {
        this.resource.contents[metadataId] = [];
      }
    }
  }

  setParent() {
    if (this.parent && this.resourceKind) {
      this.resource.contents[SystemMetadata.PARENT.id][0] = new MetadataValue(this.parent.id);
    }
  }

  targetPlacesChanged() {
    if (this.targetPlaces) {
      const assigneeMetadataIds = flatten(
        this.targetPlaces.map(place => numberKeysByValue(place.restrictingMetadataIds, RequirementState.ASSIGNEE))
      );
      const autoAssignMetadataIds = flatten(
        this.targetPlaces.map(place => numberKeysByValue(place.restrictingMetadataIds, RequirementState.AUTOASSIGN))
      );
      this.requiredMetadataIds = flatten(
        this.targetPlaces.map(place => numberKeysByValue(place.restrictingMetadataIds, RequirementState.REQUIRED))
      ).concat(assigneeMetadataIds);
      this.lockedMetadataIds = flatten(
        this.targetPlaces.map(place => numberKeysByValue(place.restrictingMetadataIds, RequirementState.LOCKED))
      ).concat(assigneeMetadataIds).concat(autoAssignMetadataIds);
    }
    this.resourceKindChanged();
  }

  resourceChanged() {
    this.resourceKindChanged();
  }

  editingDisabledForMetadata(metadata: Metadata): boolean {
    const isParent = metadata.id == SystemMetadata.PARENT.id;
    return (isParent && this.disableParent) || this.metadataIsLocked(metadata);
  }

  metadataIsRequired(metadata: Metadata): boolean {
    return !this.skipValidation
      && inArray(metadata.id, this.requiredMetadataIdsForTransition)
      || inArray(metadata.id, this.requiredMetadataIds);
  }

  metadataIsLocked(metadata: Metadata): boolean {
    return !this.skipValidation && inArray(metadata.id, this.lockedMetadataIds);
  }

  metadataDeterminesAssignee(metadata: Metadata): boolean {
    return this.resource.transitionAssigneeMetadata[metadata.id] !== undefined
      && this.resource.transitionAssigneeMetadata[metadata.id].length > 0;
  }
}
