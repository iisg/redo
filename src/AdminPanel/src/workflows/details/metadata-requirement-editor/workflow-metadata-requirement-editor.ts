import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {Workflow} from "../../workflow";
import {Metadata} from "../../../resources-config/metadata/metadata";
import {MetadataRepository} from "../../../resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class WorkflowMetadataRequirementEditor implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) workflow: Workflow;

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  async attached() {
    this.metadataList = await this.metadataRepository.getList();
  }
}
