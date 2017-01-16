import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "../../common/validation/rules/required-in-all-languages";

export interface MultilingualTextType extends StringStringMap {
}

export class Metadata {
  id: number;
  name: String = '';
  label: MultilingualTextType = {};
  placeholder: MultilingualTextType = {};
  description: MultilingualTextType = {};
  control: String = 'text';
}

export class ResourceKindMetadata extends Metadata {
  base: Metadata;
}

export function registerMetadataValidationRules() {
  ValidationRules
    .ensure('label').displayName('Label').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('control').displayName('Control').required()
    .ensure('name').displayName('Name').required()
    .on(Metadata);
}
