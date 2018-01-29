import {MetadataArrayConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class MinCountConstraintValidator extends MetadataArrayConstraintValidator {
  constructor(private i18n: I18N) {
    super();
  }

  validate(values: any[], minCount: number): boolean {
    return values.length >= minCount;
  }

  getErrorMessage(minCount): string {
    return this.i18n.tr("metadata_constraints::Value in this metadata is required");
  }
}
