import * as changeCase from "change-case";

export abstract class ConstraintValidator {
  validatedConstraintName(): string {
    const className = this.constructor.name;
    const camelCase = changeCase.camelCase(className);
    return camelCase.replace(/ConstraintValidator$/, '');
  }

  abstract validate(values: any[], config): boolean|Promise<boolean>;
}

export abstract class BackendConstraintValidator extends ConstraintValidator {
  abstract get endpointName(): string;

  /**
   * If this method returns a boolean value, backend query will be skipped and the returned value will be considered the validation result.
   * Returning undefined means that validation result can't be determined in frontend and backend query is necessary.
   * Overload this method to avoid unnecessary backend requests in cases when validation result can be determined in frontend.
   */
  validateOnFrontend(content: Object): boolean|undefined {
    return undefined;
  }
}
