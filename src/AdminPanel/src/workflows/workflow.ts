import {ValidationRules} from "aurelia-validation";
import {MultilingualTextType} from "../resources-config/metadata/metadata";
import {RequiredInAllLanguagesValidationRule} from "../common/validation/rules/required-in-all-languages";

export class Workflow {
  id: number;
  name: MultilingualTextType = {};
  enabled: boolean;
  places: Array<WorkflowPlace> = [];
  transitions: Array<WorkflowTransition> = [];
  diagram: string;
  thumbnail;
}

export interface WorkflowPlace {
  id: string;
  label: MultilingualTextType;
}

export interface WorkflowTransition {
  id: string;
  label: MultilingualTextType;
  froms: Array<string>;
  tos: Array<string>;
}

export function registerWorkflowValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').required().satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .on(Workflow);
}
