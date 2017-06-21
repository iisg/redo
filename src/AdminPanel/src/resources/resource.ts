import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";
import {WorkflowPlace, WorkflowTransition, UnsatisfiedTransitionExplanation} from "workflows/workflow";

export class Resource {
  id: number;
  kind: ResourceKind;
  currentPlaces: WorkflowPlace[];
  availableTransitions: WorkflowTransition[] = [];
  unsatisfiedTransitions: StringMap<UnsatisfiedTransitionExplanation> = {};
  contents: StringArrayMap = {};

  public canApplyTransition(transition: WorkflowTransition) {
    return this.unsatisfiedTransitions[transition.id] == undefined;
  }

  public getUnsatisfiedTransitionExplanation(transition: WorkflowTransition) {
    return this.unsatisfiedTransitions[transition.id];
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => contents[metadataId].length > 0).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
