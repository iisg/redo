import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class MinMaxValueConstraintValidator extends SingleValueConstraintValidator {
  validatedConstraintName(): string {
    return 'minMaxValue';
  }

  constructor(private i18n: I18N) {
    super();
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    const minMax = metadata.constraints.minMaxValue;
    return minMax && (minMax.min !== undefined || minMax.max !== undefined);
  }

  validate(value: number, metadata: Metadata, resource: Resource): boolean {
    const minMax = metadata.constraints.minMaxValue;
    return value
      ? (minMax.min == undefined || value >= minMax.min) && (minMax.max == undefined || value <= minMax.max)
      : true;
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    const minMaxValue = metadata.constraints.minMaxValue;
    if (minMaxValue.min != undefined && minMaxValue.max == undefined) {
      return this.i18n.tr("metadata_constraints::Value must be at least {{minMaxValue.min}}", {minMaxValue});
    } else if (minMaxValue.min == undefined && minMaxValue.max != undefined) {
      return this.i18n.tr("metadata_constraints::Value must be at most {{minMaxValue.max}}", {minMaxValue});
    } else {
      return this.i18n.tr("metadata_constraints::Value must be between {{minMaxValue.min}} and {{minMaxValue.max}}", {minMaxValue});
    }
  }
}
