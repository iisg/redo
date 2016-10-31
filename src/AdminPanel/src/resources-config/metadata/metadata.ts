import {ValidationRules} from "aurelia-validation";
import {RequiredInMainLanguageValidationRule} from "../../common/validation/rules/required-in-main-language";

export class Metadata {
  label: Object = {};
  placeholder: Object = {};
  description: Object = {};
  control: String = 'text';
}

ValidationRules
  .ensure('label').displayName("Nazwa").satisfiesRule(RequiredInMainLanguageValidationRule.NAME)
  .ensure('control').displayName("Kontrolka").required()
  .on(Metadata);
