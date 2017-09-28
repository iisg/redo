import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {arraysEqual} from "common/utils/array-utils";
import {twoWay} from "common/components/binding-mode";
import {SystemResourceKinds} from "../resource-kind/system-resource-kinds";
import {Workflow} from "workflows/workflow";
import {Metadata} from "./metadata";
import {WorkflowRepository} from "workflows/workflow-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceKindConstraintEditor {
  @bindable(twoWay) metadata: Metadata;
  @bindable idsFromBaseMetadata: number[];
  @bindable(twoWay) disabled: boolean = false;

  workflowsUsingMetadataAsAssignee: Workflow[] = [];
  loadingWorkflows: boolean = false;

  constructor(private workflowRepository: WorkflowRepository) {
  }

  metadataChanged(): void {
    this.workflowsUsingMetadataAsAssignee = [];
    this.loadingWorkflows = true;
    this.workflowRepository.getByAssigneeMetadata(this.metadata)
      .then(workflows => this.workflowsUsingMetadataAsAssignee = workflows)
      .finally(() => this.loadingWorkflows = false);
  }

  resetToBaseIds() {
    this.metadata.constraints.resourceKind = (this.idsFromBaseMetadata || []).slice();
  }

  @computedFrom('metadata.constraints.resourceKind', 'idsFromBaseMetadata')
  get wasModified(): boolean {
    return !arraysEqual(this.metadata.constraints.resourceKind, (this.idsFromBaseMetadata || []));
  }

  @computedFrom('idsFromBaseMetadata', 'disabled')
  get canInherit(): boolean {
    return !this.disabled && this.idsFromBaseMetadata != undefined;
  }

  @computedFrom('metadata.constraints.resourceKind')
  get allowsOnlyUsers(): boolean {
    return arraysEqual(this.metadata.constraints.resourceKind, [SystemResourceKinds.USER_ID]);
  }

  setToUserOnly(): void {
    this.metadata.constraints.resourceKind = [SystemResourceKinds.USER_ID];
  }

  @computedFrom('disabled', 'workflowsUsingMetadataAsAssignee', 'metadata.constraints.resourceKind')
  get dropdownDisabled(): boolean {
    return this.disabled
      || (this.workflowsUsingMetadataAsAssignee !== undefined && this.workflowsUsingMetadataAsAssignee.length > 0 && this.allowsOnlyUsers);
  }
}
