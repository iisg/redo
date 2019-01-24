import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {MinMaxValue} from "resources-config/metadata/metadata-min-max-value";

@autoinject
export class MinMaxValueConstraintValidator extends SingleValueConstraintValidator {
  validatedConstraintName(): string {
    return 'minMaxValue';
  }

  constructor(private i18n: I18N) {
    super();
  }

  validate(value: number, minMaxValue: MinMaxValue): boolean {
    return (minMaxValue.min != undefined || minMaxValue.max != undefined) ?
      (minMaxValue.min == undefined || value >= minMaxValue.min) && (minMaxValue.max == undefined || value <= minMaxValue.max) : true;
  }

  getErrorMessage(minMaxValue): string {
    if (minMaxValue.min != undefined && minMaxValue.max == undefined) {
      return this.i18n.tr("metadata_constraints::Value must be at least {{minMaxValue.min}}", {minMaxValue});
    } else if (minMaxValue.min == undefined && minMaxValue.max != undefined) {
      return this.i18n.tr("metadata_constraints::Value must be at most {{minMaxValue.max}}", {minMaxValue});
    } else {
      return this.i18n.tr("metadata_constraints::Value must be between {{minMaxValue.min}} and {{minMaxValue.max}}", {minMaxValue});
    }
  }
}
