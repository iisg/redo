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
}
