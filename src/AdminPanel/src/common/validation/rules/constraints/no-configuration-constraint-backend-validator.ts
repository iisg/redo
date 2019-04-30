import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {BackendValidation} from "../../backend-validation";
import {I18N} from "aurelia-i18n";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";

@autoinject
export class NoConfigurationConstraintBackendValidator extends SingleValueConstraintValidator {
  constraintName: string;

  validatedConstraintName(): string {
    return this.constraintName || 'any';
  }

  constructor(private backendValidation: BackendValidation, i18n: I18N) {
    super(i18n);
  }

  public forConstraint(constraintName: string): NoConfigurationConstraintBackendValidator {
    const instance = new NoConfigurationConstraintBackendValidator(this.backendValidation, this.i18n);
    instance.constraintName = constraintName;
    return instance;
  }

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return !!metadata.constraints[this.constraintName];
  }

  validate(value: string, metadata: Metadata, resource: Resource): Promise<boolean> {
    return this.backendValidation.validate(this.validatedConstraintName(), value, metadata, resource);
  }

  getErrorMessage(metadata: Metadata, resource: Resource): string {
    return this.i18n.tr(`metadata_constraints::${this.constraintName}_failed`, {metadata, resource});
  }
}
