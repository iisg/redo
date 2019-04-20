import {Metadata, MetadataConstraints} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";
import {autoinject} from "aurelia-dependency-injection";
import {FluentRuleCustomizer, ValidationRules} from "aurelia-validation";
import {MetadataConstraintValidators} from "./metadata-constraint-validators";
import {NoConfigurationConstraintBackendValidator} from "./constraints/no-configuration-constraint-backend-validator";

@autoinject
export class SingleMetadataValueValidator {

  constructor(private metadataConstraintValidators: MetadataConstraintValidators,
              private noConfigurationConstraintValidator: NoConfigurationConstraintBackendValidator) {
  }

  public createRules(metadata: Metadata, resource: Resource, required: boolean = false): FluentRuleCustomizer<any, any> {
    const rules = required ? ValidationRules.ensure('value').required() : ValidationRules.ensure('value').satisfies(() => true);
    for (const constraint of MetadataConstraints.getSupportedConstraints(metadata)) {
      let validator = this.metadataConstraintValidators.singleValueValidators[constraint.name];
      if (!validator && !this.metadataConstraintValidators.metadataArrayValidators[constraint.name]) {
        validator = this.noConfigurationConstraintValidator.forConstraint(constraint.name);
      }
      if (validator) {
        validator.addRule(rules, metadata, resource);
      }
    }
    return rules;
  }
}
