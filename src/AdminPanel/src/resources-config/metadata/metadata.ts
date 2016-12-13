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

ValidationRules
  .ensure('label').displayName("Nazwa wyświetlana").satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
  .ensure('control').displayName("Kontrolka").required()
  .ensure('name').displayName("Nazwa").required()
  .on(Metadata);
