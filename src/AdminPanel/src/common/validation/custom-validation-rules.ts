import {ValidationRules} from "aurelia-validation";
import {autoinject, Container} from "aurelia-dependency-injection";
import {RequiredInAllLanguagesValidationRule} from "./rules/required-in-all-languages";
import {registerLanguageValidationRules} from "resources-config/language-config/language";
import {registerResourceValidationRules} from "resources/resource";
import {registerMetadataValidationRules} from "resources-config/metadata/metadata";
import {registerMetadataConstraintsValidationRules} from "resources-config/metadata/metadata";
import {registerResourceKindValidationRules} from "resources-config/resource-kind/resource-kind";
import {registerWorkflowValidationRules} from "workflows/workflow";
import {registerUserRoleValidationRules} from "users/roles/user-role";
import {containsDuplicates} from "../utils/array-utils";

@autoinject
export class CustomValidationRules {
  private static readonly CUSTOM_RULES: Function[] = [
    RequiredInAllLanguagesValidationRule,
  ];

  private static readonly CUSTOM_VALIDATION_RULES_REGISTERERS: Function[] = [
    registerMetadataValidationRules,
    registerResourceKindValidationRules,
    registerResourceValidationRules,
    registerLanguageValidationRules,
    registerWorkflowValidationRules,
    registerUserRoleValidationRules,
    registerMetadataConstraintsValidationRules,
  ];

  constructor(private container: Container) {
  }

  register() {
    const rules: CustomValidationRule[] = CustomValidationRules.CUSTOM_RULES.map(ruleClass => this.container.get(ruleClass));
    const ruleNames = rules.map(rule => rule.name());
    if (containsDuplicates(ruleNames)) {
      // Possibly someone has used RuleClass.name instead of static strings and minification lead to a name clash.
      // Would be useful: https://github.com/Microsoft/TypeScript/issues/1579
      throw new Error("Some rules returned identical names from their name() methods. It may be a developer mistake or a minification" +
        " side effect. Ensure that name() return values are not calculated in runtime.");
    }
    for (const rule of rules) {
      ValidationRules.customRule(rule.name(), rule.validationFunction(), undefined);
    }
    for (let customRulesRegisterer of CustomValidationRules.CUSTOM_VALIDATION_RULES_REGISTERERS) {
      customRulesRegisterer();
    }
  }
}

export interface CustomValidationRule {
  name(): string;
  validationFunction(): (value: any, object?: any, ...args: any[]) => boolean|Promise<boolean>;
}
