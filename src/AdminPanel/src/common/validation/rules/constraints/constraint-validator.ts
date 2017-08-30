import * as changeCase from "change-case";

export abstract class ConstraintValidator {
  validatedConstraintName(): string {
    const className = this.constructor.name;
    const camelCase = changeCase.camelCase(className);
    return camelCase.replace(/ConstraintValidator$/, '');
  }

  abstract validate(values: any[], config): boolean;
}
