import {Metadata, MultilingualText} from "../metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Workflow} from "workflows/workflow";
import {WorkflowRepository} from "workflows/workflow-repository";
import {autoinject} from "aurelia-dependency-injection";
import {deepCopy} from "../../common/utils/object-utils";

@autoinject
export class ResourceKind {
  constructor(private workflowRepository?: WorkflowRepository) {
  }

  id: number;
  label: MultilingualText = {};
  metadataList: Metadata[] = [];
  workflowId: number;

  private workflowInstance: Workflow;

  public get workflow(): Workflow {
    if (!this.workflowInstance) {
      this.getWorkflow();
    }
    return this.workflowInstance;
  }

  public set workflow(workflow: Workflow) {
    this.workflowInstance = workflow;
    this.workflowId = workflow.id;
  }

  public getWorkflow(): Promise<Workflow> {
    if (!this.workflowId) {
      return Promise.resolve(undefined);
    } else if (this.workflowInstance) {
      return Promise.resolve(this.workflowInstance);
    } else {
      return this.workflowRepository.get(this.workflowId).then(workflow => {
        return this.workflow = workflow;
      });
    }
  }

  public static clone(resourceKind: Object): ResourceKind {
    let cloned = deepCopy(resourceKind);
    cloned.metadataList = cloned.metadataList.map(metadata => Metadata.clone(metadata));
    return cloned;
  }
}

export function registerResourceKindValidationRules() {
  ValidationRules
    .ensure('label').displayName("Label").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('metadataList').displayName('Metadata').satisfies((metadataList: Metadata[]) => metadataList.length > 0)
    .withMessageKey('mustHaveMetadata')
    .on(ResourceKind);
}
