import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";

@autoinject
export class RegexConstraintValidator extends SingleValueConstraintValidator {
  constructor(private backendValidation: BackendValidation, private i18n: I18N) {
    super();
  }

  validate(value: string, regex: string): Promise<boolean> {
    if (!regex || !value) {
      return Promise.resolve(true);
    } else {
      return this.backendValidation.getResult(this.validatedConstraintName(), {regex, value});
    }
  }

  getErrorMessage(regex): string {
    return this.i18n.tr("metadata_constraints::Value must fit the regular expression: {{regex}}", {regex});
  }
}
