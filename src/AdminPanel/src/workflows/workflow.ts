import {ValidationRules} from "aurelia-validation";
import {MultilingualText} from "resources-config/metadata/metadata";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Entity} from "common/entity/entity";
import {deepCopy} from "common/utils/object-utils";
import {automapped, map} from "common/dto/decorators";
import {RestrictingMetadataMapper} from "./workflow-mapping";

@automapped
export class WorkflowPlace {
  static NAME = 'WorkflowPlace';

  @map id: string;
  @map label: MultilingualText;
  @map(RestrictingMetadataMapper) restrictingMetadataIds: RestrictingMetadataIdMap;

  constructor(id?: string, label?: MultilingualText, restrictingMetadataIds?: RestrictingMetadataIdMap) {
    this.id = id;
    this.label = label;
    this.restrictingMetadataIds = restrictingMetadataIds;
  }
}

export type RestrictingMetadataIdMap = NumberMap<RequirementState>;

export enum RequirementState {
  OPTIONAL,
  REQUIRED,
  LOCKED,
  ASSIGNEE,
}

@automapped
export class WorkflowTransition {
  static NAME = 'WorkflowTransition';

  @map id: string;
  @map label: MultilingualText;
  @map('string[]') froms: string[];
  @map('string[]') tos: string[];
  @map('string[]') permittedRoleIds: string[];
}

@automapped
export class Workflow extends Entity {
  static NAME = 'Workflow';

  @map id: number;
  @map name: MultilingualText = {};
  @map('WorkflowPlace[]') places: WorkflowPlace[] = [];
  @map('WorkflowTransition[]') transitions: WorkflowTransition[] = [];
  @map diagram: string;
  @map thumbnail;
  @map resourceClass: string;

  copyFrom(workflow: Workflow) {
    $.extend(this, workflow);
    this.places = deepCopy(workflow.places);
    this.transitions = deepCopy(workflow.transitions);
  }
}

export interface TransitionBlockReason {
  missingMetadataIds: number[];
  userMissingRequiredRole: boolean;
  otherUserAssigned: boolean;
}

export function registerWorkflowValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').required().satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('places').displayName('Places').required().satisfies(places => places.length > 0)
    .withMessageKey('Workflow must consist of at least one place')
    .on(Workflow);
}
