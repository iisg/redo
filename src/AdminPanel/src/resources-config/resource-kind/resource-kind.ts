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
    if (!this.metadataList.find(m => m.id === SystemMetadata.PARENT.id)) {
      this.metadataList.unshift(SystemMetadata.PARENT);
    }
  }
}

export function registerResourceKindValidationRules() {
  ValidationRules
    .ensure('label').displayName("Label").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('metadataList').displayName('Metadata')
    .satisfies((metadataList: Metadata[]) => metadataList.filter(m => !!m.resourceClass).length > 0)
    .withMessageKey('mustHaveMetadata')
    .on(ResourceKind);
}
