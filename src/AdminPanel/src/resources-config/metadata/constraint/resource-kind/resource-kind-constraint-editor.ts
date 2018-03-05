import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {arraysEqual} from "common/utils/array-utils";
import {oneTime, twoWay} from "common/components/binding-mode";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {SystemResourceKinds} from "resources-config/resource-kind/system-resource-kinds";

@autoinject
export class ResourceKindConstraintEditor {
  @bindable(twoWay) metadata: Metadata;
  @bindable(oneTime) idsFromOriginalMetadata: number[];
  @bindable(twoWay) disabled: boolean = false;
  @bindable hasBase: boolean;
  @bindable resourceClass: string;

  workflowsUsingMetadataAsAssignee: Workflow[] = [];
  loadingWorkflows: boolean = false;

  constructor(private workflowRepository: WorkflowRepository) {
  }

  metadataChanged(): void {
    if (this.metadata === undefined || this.metadata.id === undefined) {
      return;
    }
    this.workflowsUsingMetadataAsAssignee = [];
    this.loadingWorkflows = true;
    this.workflowRepository.getByAssigneeMetadata(this.metadata)
      .then(workflows => this.workflowsUsingMetadataAsAssignee = workflows)
      .finally(() => this.loadingWorkflows = false);
  }

  resetToOriginalIds() {
    this.metadata.constraints.resourceKind = (this.idsFromOriginalMetadata || []).slice();
  }

  @computedFrom('metadata.constraints.resourceKind', 'idsFromOriginalMetadata')
  get wasModified(): boolean {
    return !arraysEqual(this.metadata.constraints.resourceKind, (this.idsFromOriginalMetadata || []));
  }

  @computedFrom('hasBase', 'disabled')
  get canInherit(): boolean {
    return !this.disabled && this.hasBase;
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
