import {Metadata, MultilingualText} from "../metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Workflow} from "workflows/workflow";
import {Entity} from "common/entity/entity";
import {automapped, map} from "common/dto/decorators";
import {SystemMetadata} from "../metadata/system-metadata";

@automapped
export class ResourceKind extends Entity {
  static NAME = 'ResourceKind';

  @map id: number;
  @map label: MultilingualText = {};
  @map('Metadata[]') metadataList: Metadata[] = [];
  @map('WorkflowId') workflow: Workflow;
  @map resourceClass: string;
  @map displayStrategies: StringStringMap = {};

  public ensureHasSystemMetadata() {
    if (this.metadataList.find(m => m.id === SystemMetadata.PARENT.id)) {
      this.metadataList.unshift(SystemMetadata.PARENT);
    }
  }
}

export function registerResourceKindValidationRules() {
  ValidationRules
    .ensure('label').displayName("Label").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
  // parent metadata with id == -1 is hidden and obligatory, it shouldn't be treated as one of metadata in list while validating
    .ensure('metadataList').displayName('Metadata').satisfies((metadataList: Metadata[]) => metadataList.length > 1)
    .withMessageKey('mustHaveMetadata')
    .on(ResourceKind);
}
