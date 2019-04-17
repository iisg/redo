import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class ValidPeselConstraintValidator extends SingleValueConstraintValidator {

  constructor(private backendValidation: BackendValidation, i18n: I18N) {
    super(i18n);
  }

  getErrorMessage(config): string {
    return this.i18n.tr("metadata_constraints::Invalid pesel number");
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.validPesel;
  }

  validate(metadataValue, metadata: Metadata, resource: Resource): boolean | Promise<boolean> {
    if (!metadataValue) {
      return true;
    }
    return this.backendValidation.getResult(this.validatedConstraintName(), {metadataValue});
  }

  validatedConstraintName(): string {
    return "validPesel";
  }
}
