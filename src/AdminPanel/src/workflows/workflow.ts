import {ValidationRules} from "aurelia-validation";
import {MultilingualText} from "resources-config/metadata/metadata";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Entity} from "common/entity/entity";
import {automapped, map} from "common/dto/decorators";
import {RestrictingMetadataMapper} from "./workflow-mapping";

@automapped
export class WorkflowPlace {
  static NAME = 'WorkflowPlace';

  @map id: string;
  @map label: MultilingualText;
  @map(RestrictingMetadataMapper) restrictingMetadataIds: RestrictingMetadataIdMap;
  @map pluginsConfig: StringMap<any> = {};

  constructor(id?: string,
              label?: MultilingualText,
              restrictingMetadataIds?: RestrictingMetadataIdMap,
              pluginsConfig: StringMap<any> = {}) {
    this.id = id;
    this.label = label;
    this.restrictingMetadataIds = restrictingMetadataIds;
    this.pluginsConfig = pluginsConfig;
  }
}

export type RestrictingMetadataIdMap = NumberMap<RequirementState>;

export enum RequirementState {
  OPTIONAL,
  REQUIRED,
  LOCKED,
  ASSIGNEE,
  AUTOASSIGN,
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
}

export interface TransitionBlockReason {
  missingMetadataIds: number[];
  otherUserAssigned: boolean;
}

export function registerWorkflowValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').required().satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('places').displayName('Places').required().satisfies(places => places.length > 0)
    .withMessageKey('Workflow must consist of at least one place')
    .on(Workflow);
}
