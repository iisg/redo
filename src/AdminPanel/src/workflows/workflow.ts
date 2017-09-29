import {ValidationRules} from "aurelia-validation";
import {MultilingualText} from "resources-config/metadata/metadata";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Entity} from "common/entity/entity";
import {deepCopy} from "common/utils/object-utils";

export class Workflow extends Entity {
  id: number;
  name: MultilingualText = {};
  enabled: boolean;
  places: WorkflowPlace[] = [];
  transitions: WorkflowTransition[] = [];
  diagram: string;
  thumbnail;

  copyFrom(workflow: Workflow) {
    $.extend(this, workflow);
    this.places = deepCopy(workflow.places);
    this.transitions = deepCopy(workflow.transitions);
  }
}

export interface WorkflowPlace {
  id: string;
  label: MultilingualText;
  restrictingMetadataIds: RestrictingMetadataIdMap;
}

export type RestrictingMetadataIdMap = NumberMap<RequirementState>;

export enum RequirementState {
  OPTIONAL,
  REQUIRED,
  LOCKED,
  ASSIGNEE,
}

export interface WorkflowTransition {
  id: string;
  label: MultilingualText;
  froms: string[];
  tos: string[];
  permittedRoleIds: string[];
}

export interface UnsatisfiedTransitionExplanation {
  missingMetadataIds: number[];
  userMissingRequiredRole: boolean;
}

export function registerWorkflowValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').required().satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('places').displayName('Places').required().satisfies(places => places.length > 0)
    .withMessageKey('Workflow must consist of at least one place')
    .on(Workflow);
}
