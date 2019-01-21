import {MetadataArrayConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class MinCountConstraintValidator extends MetadataArrayConstraintValidator {
  validatedConstraintName(): string {
    return 'minCount';
  }

  constructor(private i18n: I18N) {
    super();
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.minCount > 0;
  }

  validate(values: any[], metadata: Metadata, resource: Resource): boolean {
    const minCount = metadata.constraints.minCount;
    return values && values.length >= minCount;
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    const minCount = metadata.constraints.minCount;
    if (minCount == 1) {
      return this.i18n.tr("metadata_constraints::Value in this metadata is required");
    } else {
      return this.i18n.tr("metadata_constraints::At least {{minCount}} values required", {minCount});
    }
  }
}
