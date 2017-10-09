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

  public canApplyTransition(transition: WorkflowTransition) {
    return this.blockedTransitions[transition.id] == undefined;
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => contents[metadataId].length > 0).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
