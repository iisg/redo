import {BackendConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";

@autoinject
export class RegexConstraintValidator extends BackendConstraintValidator {
  constructor(private backendValidation: BackendValidation) {
    super();
  }

  validate(values: string[], regex: string): Promise<boolean> {
    return this.backendValidation.getResult(this, {regex, values});
  }

  get endpointName(): string {
    return 'regex';
  }

  validateOnFrontend(content: {regex: string, values: string[]}): boolean|any {
    return (!content.regex || content.values.length === 0)
      ? true
      : undefined;
  }
}
