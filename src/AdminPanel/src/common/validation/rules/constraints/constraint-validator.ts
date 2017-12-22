import * as changeCase from "change-case";
import {FluentRuleCustomizer} from "aurelia-validation";

export abstract class ConstraintValidator {
  validatedConstraintName(): string {
    const className = this.constructor.name;
    const camelCase = changeCase.camelCase(className);
    return camelCase.replace(/ConstraintValidator$/, '');
  }

  public addRule(rules: FluentRuleCustomizer<any, any>, config): FluentRuleCustomizer<any, any> {
    return rules.satisfies(value => this.validate(value, config), this.getRuleConfig()).withMessage(this.getErrorMessage(config));
  }

  abstract getErrorMessage(config): string;

  getRuleConfig(): any {
    return {};
  }

  abstract validate(value, config): boolean | Promise<boolean>;
}

export abstract class SingleValueConstraintValidator extends ConstraintValidator {
}

export abstract class MetadataArrayConstraintValidator extends ConstraintValidator {
  abstract validate(values: any[], config): boolean | Promise<boolean>;
}
