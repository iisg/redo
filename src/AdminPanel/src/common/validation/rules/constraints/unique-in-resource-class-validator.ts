import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";
import {MetadataLabelValueConverter} from "../../../../resources-config/metadata/metadata-label";

@autoinject
export class UniqueInResourceClassValidator extends SingleValueConstraintValidator {

  constructor(private backendValidation: BackendValidation,
              private i18n: I18N,
              private metadataLabel: MetadataLabelValueConverter) {
    super();
  }

  getErrorMessage(config): string {
    return this.i18n.tr(
      "metadata_constraints::{{metadata}} with given value already exists",
      {metadata: this.metadataLabel.toView(config)}
    );
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return metadata.constraints.uniqueInResourceClass;
  }

  validate(metadataValue, metadata: Metadata, resource: Resource): boolean | Promise<boolean> {
    if (!metadataValue) {
      return true;
    }
    if (Array.isArray(metadataValue)) {
      const values = metadataValue.map(m => m.value);
      return (new Set(values)).size === values.length;
    } else {
      return this.backendValidation.getResult(
        this.validatedConstraintName(),
        {
          resourceClass: metadata.resourceClass,
          metadataId: metadata.id,
          resourceId: resource.id,
          metadataValue
        }
      );
    }
  }

  validatedConstraintName(): string {
    return "uniqueInResourceClass";
  }
}
