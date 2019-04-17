import {MetadataArrayConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class MaxCountConstraintValidator extends MetadataArrayConstraintValidator {
  validatedConstraintName(): string {
    return 'maxCount';
  }

  constructor(i18n: I18N) {
    super(i18n);
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.maxCount > 0;
  }

  validate(values: any[], metadata: Metadata, resource: Resource): boolean {
    const maxCount = metadata.constraints.maxCount;
    return !maxCount || maxCount == -1 || !values || values.length <= maxCount;
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    return this.i18n.tr("metadata_constraints::No more than {{maxCount}} values allowed", {maxCount: metadata.constraints.maxCount});
  }
}
