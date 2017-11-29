import {Metadata, MultilingualText} from "../metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Workflow} from "workflows/workflow";
import {Entity} from "common/entity/entity";
import {automapped, map, arrayOf} from "common/dto/decorators";

@automapped
export class ResourceKind extends Entity {
  static NAME = 'ResourceKind';

  @map id: number;
  @map label: MultilingualText = {};
  @map(arrayOf(Metadata)) metadataList: Metadata[] = [];
  @map('WorkflowId') workflow: Workflow;
  @map resourceClass: string;
}

export function registerResourceKindValidationRules() {
  ValidationRules
    .ensure('label').displayName("Label").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('metadataList').displayName('Metadata').satisfies((metadataList: Metadata[]) => metadataList.length > 0)
    .withMessageKey('mustHaveMetadata')
    .on(ResourceKind);
}
