import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "../../common/validation/rules/required-in-all-languages";

export class Metadata {
  id: number;
  name: String = '';
  label: Object = {};
  placeholder: Object = {};
  description: Object = {};
  control: String = 'text';
}

export class ResourceKindMetadata extends Metadata {
  base: Metadata;
}

// ugly hack to disable the rules in the unit testing, see: https://github.com/aurelia/validation/issues/377#issuecomment-267791805
if ((ValidationRules as any).parser) {
  ValidationRules
    .ensure('label').displayName('Label').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('control').displayName('Control').required()
    .ensure('name').displayName('Name').required()
    .on(Metadata);
}
