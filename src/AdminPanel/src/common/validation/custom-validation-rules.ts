import {validationMessages, ValidationRules} from "aurelia-validation";
import {autoinject, Container} from "aurelia-dependency-injection";
import {RequiredInMainLanguageValidationRule} from "./rules/required-in-main-language";

@autoinject
export class CustomValidationRules {
  private static readonly CUSTOM_RULES: Function[] = [
    RequiredInMainLanguageValidationRule
  ];

  constructor(private container: Container) {
  }

  register() {
    for (let customRuleClass of CustomValidationRules.CUSTOM_RULES) {
      let rule: CustomValidationRule = this.container.get(customRuleClass);
      ValidationRules.customRule(rule.name(), rule.validationFunction(), rule.message());
    }
  }
}

export interface CustomValidationRule {
  name(): string;
  message(): string;
  validationFunction(): (value: any, object?: any, ...args: any[]) => boolean | Promise<boolean>;
}

validationMessages['required'] = `\${$displayName} jest wartością wymaganą.`;
