import {Metadata, ResourceKindMetadata} from "../metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "../../common/validation/rules/required-in-all-languages";

export class ResourceKind {
  id: number;
  label: Object = {};
  metadataList: ResourceKindMetadata[] = [];
}

ValidationRules
  .ensure('label').displayName("Nazwa wyświetlana").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
  .ensure('metadataList').displayName('Metadane').satisfies((metadataList: Metadata[]) => metadataList.length > 0)
  .withMessage('Rodzaj zasobu musi posiadać metadane.')
  .on(ResourceKind);
