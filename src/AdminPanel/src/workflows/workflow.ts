import {ValidationRules} from "aurelia-validation";
import {MultilingualText} from "resources-config/metadata/metadata";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";

export class Workflow {
  id: number;
  name: MultilingualText = {};
  enabled: boolean;
  places: Array<WorkflowPlace> = [];
  transitions: Array<WorkflowTransition> = [];
  diagram: string;
  thumbnail;
}

export interface WorkflowPlace {
  id: string;
  label: MultilingualText;
}

export interface WorkflowTransition {
  id: string;
  label: MultilingualText;
  froms: Array<string>;
  tos: Array<string>;
  permittedRoleIds: Array<string>;
}

export function registerWorkflowValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').required().satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .on(Workflow);
}
