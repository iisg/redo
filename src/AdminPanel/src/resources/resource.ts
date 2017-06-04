import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";
import {WorkflowPlace, WorkflowTransition} from "workflows/workflow";

export class Resource {
  id: number;
  kind: ResourceKind;
  currentPlaces: WorkflowPlace[];
  availableTransitions: WorkflowTransition[] = [];
  possibleTransitions: WorkflowTransition[] = [];
  contents: StringArrayMap = {};

  public canApplyTransition(transition: WorkflowTransition) {
    const permittedTransitionIds = this.possibleTransitions.map(transition => transition.id);
    return permittedTransitionIds.indexOf(transition.id) != -1;
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => contents[metadataId].length > 0).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
