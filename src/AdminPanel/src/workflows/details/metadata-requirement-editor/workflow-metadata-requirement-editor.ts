import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode, computedFrom} from "aurelia-binding";
import {Workflow, WorkflowPlace} from "../../workflow";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowPlaceSorter} from "./workflow-place-sorter";

@autoinject
export class WorkflowMetadataRequirementEditor implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) workflow: Workflow;

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
    const checked = changedPlace.requiredMetadataIds.indexOf(metadata.id) > -1;
    const changedPlaceIndex = this.orderedPlaces.indexOf(changedPlace);
    const placesToUpdate = this.orderedPlaces.slice(changedPlaceIndex + 1);
    for (const place of placesToUpdate) {
      this.setMetadataRequirement(metadata, place, checked);
    }
  }

  private setMetadataRequirement(metadata: Metadata, place: WorkflowPlace, required: boolean) {
    const index = place.requiredMetadataIds.indexOf(metadata.id);
    const currentlyRequired = index > -1;
    if (currentlyRequired && !required) {
      place.requiredMetadataIds.splice(index, 1);
    } else if (!currentlyRequired && required) {
      place.requiredMetadataIds.push(metadata.id);
    }
  }
}
