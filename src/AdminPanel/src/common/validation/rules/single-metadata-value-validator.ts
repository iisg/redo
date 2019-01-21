import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";
import {autoinject} from "aurelia-dependency-injection";
import {FluentRuleCustomizer, ValidationRules} from "aurelia-validation";
import {MetadataConstraintValidators} from "./metadata-constraint-validators";

@autoinject
export class SingleMetadataValueValidator {

  constructor(private metadataConstraintValidators: MetadataConstraintValidators) {
  }

  public createRules(metadata: Metadata, resource: Resource, required: boolean = false): FluentRuleCustomizer<any, any> {
    const rules = required ? ValidationRules.ensure('value').required() : ValidationRules.ensure('value').satisfies(() => true);
    for (const constraintName in metadata.constraints) {
      if (metadata.constraints.hasOwnProperty(constraintName)) {
        const validator = this.metadataConstraintValidators.singleValueValidators[constraintName];
        if (validator) {
          validator.addRule(rules, metadata, resource);
        }
      }
    }
    return rules;
  }
}
