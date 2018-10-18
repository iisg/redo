import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";
import {autoinject} from "aurelia-dependency-injection";
import {FluentRuleCustomizer, ValidationRules} from "aurelia-validation";
import {MetadataConstraintValidators} from "./metadata-constraint-validators";

@autoinject
export class SingleMetadataValueValidator {

  constructor(private metadataConstraintValidators: MetadataConstraintValidators) {
  }

  public createRules(metadata: Metadata, resource: Resource): FluentRuleCustomizer<any, any> {
    const rules = ValidationRules.ensure('value').required();
    for (const constraintName in metadata.constraints) {
      if (metadata.constraints.hasOwnProperty(constraintName)) {
        const validator = this.metadataConstraintValidators.singleValueValidators[constraintName];
        if (validator) {
          const constraintArgument = metadata.constraints[constraintName];
          validator.addRule(rules, constraintArgument);
        }
      }
    }
    return rules;
  }
}
