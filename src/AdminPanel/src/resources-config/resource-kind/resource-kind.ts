import {Metadata, ResourceKindMetadata} from "../metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "../../common/validation/rules/required-in-all-languages";

export class ResourceKind {
  id: number;
  label: Object = {};
  metadataList: ResourceKindMetadata[] = [];
}

ValidationRules
  .ensure('label').displayName("Label").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
  .ensure('metadataList').displayName('Metadata').satisfies((metadataList: Metadata[]) => metadataList.length > 0)
  .withMessageKey('mustHaveMetadata')
  .on(ResourceKind);
