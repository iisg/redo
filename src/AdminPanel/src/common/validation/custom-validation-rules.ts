import {ValidationRules} from "aurelia-validation";
import {autoinject, Container} from "aurelia-dependency-injection";
import {RequiredInAllLanguagesValidationRule} from "./rules/required-in-all-languages";
import {registerLanguageValidationRules} from "../../resources-config/language-config/language";
import {registerResourceValidationRules} from "../../resources/resource";
import {registerMetadataValidationRules} from "../../resources-config/metadata/metadata";
import {registerResourceKindValidationRules} from "../../resources-config/resource-kind/resource-kind";

@autoinject
export class CustomValidationRules {
  private static readonly CUSTOM_RULES: Function[] = [
    RequiredInAllLanguagesValidationRule
  ];

  private static readonly CUSTOM_VALIDATION_RULES_REGISTERERS: Function[] = [
    registerMetadataValidationRules,
    registerResourceKindValidationRules,
    registerResourceValidationRules,
    registerLanguageValidationRules
  ];

  constructor(private container: Container) {
  }

  register() {
    for (let customRuleClass of CustomValidationRules.CUSTOM_RULES) {
      const rule: CustomValidationRule = this.container.get(customRuleClass);
      ValidationRules.customRule(rule.name(), rule.validationFunction(), undefined);
    }
    for (let customRulesRegisterer of CustomValidationRules.CUSTOM_VALIDATION_RULES_REGISTERERS) {
      customRulesRegisterer();
    }
  }
}

export interface CustomValidationRule {
  name(): string;
  validationFunction(): (value: any, object?: any, ...args: any[]) => boolean | Promise<boolean>;
}
