import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {FluentRuleCustomizer, ValidationRules} from "aurelia-validation";
import {MetadataConstraintValidators} from "./metadata-constraint-validators";
import {Resource} from "../../../resources/resource";

@autoinject
export class AllMetadataValueValidator {

  constructor(private metadataConstraintValidators: MetadataConstraintValidators) {
  }

  public createRules(metadata: Metadata, resource: Resource): FluentRuleCustomizer<any, any> {
    let rules = ValidationRules.ensure(metadata.id + '').satisfies(() => true);
    for (const constraintName in metadata.constraints) {
      if (metadata.constraints.hasOwnProperty(constraintName)) {
        const validator = this.metadataConstraintValidators.metadataArrayValidators[constraintName];
        if (validator) {
          validator.addRule(rules, metadata, resource);
        }
      }
    }
    // hack to support integer attributes, see https://github.com/aurelia/validation/issues/474
    rules.rules.forEach(rules => rules.forEach(rule => rule.property.name = parseInt(rule.property.name as string)));
    return rules;
  }
}
