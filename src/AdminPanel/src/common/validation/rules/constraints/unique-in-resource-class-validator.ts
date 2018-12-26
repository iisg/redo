import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";

@autoinject
export class UniqueInResourceClassValidator extends SingleValueConstraintValidator {

  constructor(private backendValidation: BackendValidation, private i18n: I18N) {
    super();
  }

  getErrorMessage(config): string {
    return this.i18n.tr("metadata_constraints::Metadata with given value already exists");
  }

  validate(metadataValue, config): boolean | Promise<boolean> {
    let uniqueConfig = config as UniqueConstraintConfig;
    return !uniqueConfig.unique || this.backendValidation.getResult(
      this.validatedConstraintName(),
      {
        resourceClass: uniqueConfig.resourceClass,
        metadataId: uniqueConfig.metadataId,
        resourceId: uniqueConfig.resourceId,
        metadataValue
      }
    );
  }

  validatedConstraintName(): string {
    return "uniqueInResourceClass";
  }
}

export class UniqueConstraintConfig {
  constructor(
    public readonly resourceId: Number,
    public readonly resourceClass: String,
    public readonly metadataId: Number,
    public readonly unique: Boolean
  ) {
  }
}
