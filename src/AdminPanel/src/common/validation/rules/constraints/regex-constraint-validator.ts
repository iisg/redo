import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class RegexConstraintValidator extends SingleValueConstraintValidator {
  validatedConstraintName(): string {
    return 'regex';
  }

  constructor(private backendValidation: BackendValidation, i18n: I18N) {
    super(i18n);
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.regex && metadata.constraints.regex.trim() !== '';
  }

  validate(value: string, metadata: Metadata, resource: Resource): Promise<boolean> {
    if (!value) {
      return Promise.resolve(true);
    } else {
      const regex = metadata.constraints.regex;
      return this.backendValidation.getResult(this.validatedConstraintName(), {regex, value});
    }
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    const regex = metadata.constraints.regex;
    return this.i18n.tr("metadata_constraints::Value must fit the regular expression: {{regex}}", {regex});
  }
}
