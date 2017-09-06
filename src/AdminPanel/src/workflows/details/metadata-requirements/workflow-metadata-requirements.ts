import {bindable, ComponentAttached} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {Workflow, WorkflowPlace} from "../../workflow";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowPlaceSorter} from "./workflow-place-sorter";
import {booleanAttribute} from "common/components/boolean-attribute";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class WorkflowMetadataRequirements implements ComponentAttached {
  @bindable(twoWay) workflow: Workflow;
  @bindable @booleanAttribute editable: boolean = false;

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository, private workflowBfs: WorkflowPlaceSorter) {
  }

  async attached() {
    this.metadataList = await this.metadataRepository.getList();
  }

  @computedFrom('workflow.places')
  get orderedPlaces(): WorkflowPlace[] {
    return this.workflowBfs.getOrderedPlaces(this.workflow);
  }

  checkboxChanged(metadata: Metadata, changedPlace: WorkflowPlace): void {
    const required = changedPlace.requiredMetadataIds.indexOf(metadata.id) > -1;
    const locked = changedPlace.lockedMetadataIds.indexOf(metadata.id) > -1;
    const changedPlaceIndex = this.orderedPlaces.indexOf(changedPlace);
    const placesToUpdate = this.orderedPlaces.slice(changedPlaceIndex + 1);
    for (const place of placesToUpdate) {
      this.setMetadataRequirement(metadata, place, required, locked);
    }
  }

  private setMetadataRequirement(metadata: Metadata, place: WorkflowPlace, required: boolean, locked: boolean) {
    // Array properties don't trigger bindings on modification, only on assignment - hence extra complicated operations here
    // https://github.com/aurelia/templating/issues/561
    let index = place.requiredMetadataIds.indexOf(metadata.id);
    const currentlyRequired = index > -1;
    if (currentlyRequired && !required) {
      const newArray = place.requiredMetadataIds.concat();
      newArray.splice(index, 1);
      place.requiredMetadataIds = newArray;
    } else if (!currentlyRequired && required) {
      place.requiredMetadataIds = place.requiredMetadataIds.concat(metadata.id);
    }
    index = place.lockedMetadataIds.indexOf(metadata.id);
    const currentlyLocked = index > -1;
    if (currentlyLocked && !locked) {
      const newArray = place.lockedMetadataIds.concat();
      newArray.splice(index, 1);
      place.lockedMetadataIds = newArray;
    } else if (!currentlyLocked && locked) {
      place.lockedMetadataIds = place.lockedMetadataIds.concat(metadata.id);
    }
  }
}
