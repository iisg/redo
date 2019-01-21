import {FluentRuleCustomizer} from "aurelia-validation";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

export abstract class ConstraintValidator {
  abstract validatedConstraintName(): string;

  public addRule(rules: FluentRuleCustomizer<any, any>, metadata: Metadata, resource: Resource): FluentRuleCustomizer<any, any> {
    if (this.shouldValidate(metadata, resource)) {
      return rules
        .satisfies(value => this.validate(value, metadata, resource))
        .withMessage(this.getErrorMessage(metadata, resource));
    }
  }

  abstract getErrorMessage(metadata: Metadata, resource: Resource): string;

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return true;
  }

  abstract validate(value, metadata: Metadata, resource: Resource): boolean | Promise<boolean>;
}

export abstract class SingleValueConstraintValidator extends ConstraintValidator {
}

export abstract class MetadataArrayConstraintValidator extends ConstraintValidator {
  abstract validate(values: any[], metadata: Metadata, resource: Resource): boolean | Promise<boolean>;
}
