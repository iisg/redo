import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {FluentRuleCustomizer, ValidationRules} from "aurelia-validation";
import {MetadataConstraintValidators} from "./metadata-constraint-validators";

@autoinject
export class AllMetadataValueValidator {

  constructor(private metadataConstraintValidators: MetadataConstraintValidators) {
  }

  public createRules(metadata: Metadata): FluentRuleCustomizer<any, any> {
    let rules = ValidationRules.ensure(metadata.id + '').satisfies(v => true);
    for (const constraintName in metadata.constraints) {
      if (metadata.constraints.hasOwnProperty(constraintName)) {
        const validator = this.metadataConstraintValidators.metadataArrayValidators[constraintName];
        if (validator) {
          const constraintArgument = metadata.constraints[constraintName];
          validator.addRule(rules, constraintArgument);
        }
      }
    }
    // hack to support integer attributes, see https://github.com/aurelia/validation/issues/474
    rules.rules.forEach(rules => rules.forEach(rule => rule.property.name = parseInt(rule.property.name) as any as string));
    return rules;
  }
}
