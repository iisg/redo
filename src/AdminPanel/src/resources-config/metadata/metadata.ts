import {ValidationRules} from "aurelia-validation";
import {RequiredInMainLanguageValidationRule} from "../../common/validation/rules/required-in-main-language";

export class Metadata {
  name: String = '';
  label: Object = {};
  placeholder: Object = {};
  description: Object = {};
  control: String = 'text';
}

ValidationRules
  .ensure('label').displayName("Nazwa wyświetlana").satisfiesRule(RequiredInMainLanguageValidationRule.NAME)
  .ensure('control').displayName("Kontrolka").required()
  .ensure('name').displayName("Nazwa").required()
  .on(Metadata);
