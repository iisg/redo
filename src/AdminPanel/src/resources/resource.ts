import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {TransitionBlockReason, WorkflowPlace, WorkflowTransition} from "workflows/workflow";
import {Entity} from "common/entity/entity";
import {copy, map, mappedWith} from "common/dto/decorators";
import {ResourceMapper} from "./resource-mapping";
import {ValidationRules} from "aurelia-validation";

@mappedWith(ResourceMapper)
export class Resource extends Entity {
  static NAME = 'Resource';

  @map id: number;
  @map('ResourceKindId') kind: ResourceKind;
  @map('WorkflowPlace[]') currentPlaces: WorkflowPlace[];
  @map('WorkflowTransition[]') availableTransitions: WorkflowTransition[] = [];
  @map blockedTransitions: StringMap<TransitionBlockReason> = {};
  @map('{WorkflowTransition[]}') transitionAssigneeMetadata: NumberMap<WorkflowTransition[]> = {};
  @copy contents: NumberMap<any[]> = {};
  @map resourceClass: string;

  public canApplyTransition(transition: WorkflowTransition): boolean {
    const blockedTransitionReason = this.blockedTransitions[transition.id];
    if (blockedTransitionReason) {
      return !(blockedTransitionReason.userMissingRequiredRole || blockedTransitionReason.otherUserAssigned);
    }
    return true;
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .on(Resource);
}
