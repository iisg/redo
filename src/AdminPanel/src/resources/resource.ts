import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";
import {WorkflowPlace, WorkflowTransition} from "workflows/workflow";

export class Resource {
  id: number;
  kind: ResourceKind;
  currentPlaces: Array<WorkflowPlace>;
  availableTransitions: Array<WorkflowTransition> = [];
  permittedTransitions: Array<WorkflowTransition> = [];
  contents: StringAnyMap = {};

  public canApplyTransition(transition: WorkflowTransition) {
    for (let permittedTransition of this.permittedTransitions) {
      if (permittedTransition.id == transition.id) {
        return true;
      }
    }
    return false;
  }
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => !!contents[metadataId]).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
