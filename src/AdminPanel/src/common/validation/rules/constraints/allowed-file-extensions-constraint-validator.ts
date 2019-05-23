import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class AllowedFileExtensionsConstraintValidator extends SingleValueConstraintValidator {
  validatedConstraintName(): string {
    return 'allowedFileExtensions';
  }

  constructor(i18n: I18N) {
    super(i18n);
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.allowedFileExtensions !== undefined;
  }

  validate(value: any, metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.allowedFileExtensions.includes(value.split('.').pop());
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    return this.i18n.tr("metadata_constraints::File must have one of these extensions: {{allowedExtensions}}",
      {allowedExtensions: metadata.constraints.allowedFileExtensions.join(', ')});
  }
}
