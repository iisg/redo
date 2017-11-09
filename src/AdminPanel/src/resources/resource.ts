import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";
import {WorkflowPlace, WorkflowTransition, TransitionBlockReason} from "workflows/workflow";
import {Entity} from "common/entity/entity";

export class Resource extends Entity {
  id: number;
  kind: ResourceKind;
  currentPlaces: WorkflowPlace[];
  availableTransitions: WorkflowTransition[] = [];
  blockedTransitions: StringMap<TransitionBlockReason> = {};
  transitionAssigneeMetadata: NumberMap<WorkflowTransition[]> = {};
  contents: StringArrayMap = {};
  resourceClass: string;

  public canApplyTransition(transition: WorkflowTransition) {
    return this.blockedTransitions[transition.id] == undefined;
  }

  public filterUndefinedValues() {
    for (let metadataId in this.contents) {
      if (this.contents[metadataId].length > 0) {
        this.contents[metadataId] = this.contents[metadataId].filter(item => item !== undefined);
      }
    }
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => contents[metadataId].length > 0).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
