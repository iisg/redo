import {MetadataArrayConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class MaxCountConstraintValidator extends MetadataArrayConstraintValidator {
  constructor(private i18n: I18N) {
    super();
  }

  validate(values: any[], maxCount: number): boolean {
    return maxCount == 0 || values.length <= maxCount;
  }

  getErrorMessage(maxCount): string {
    return this.i18n.tr("metadata_constraints::No more than {{maxCount}} values allowed", {maxCount});
  }
}
