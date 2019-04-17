import {FluentRuleCustomizer} from "aurelia-validation";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {Resource} from "../../../../resources/resource";
import {I18N} from "aurelia-i18n";
import {FrontendConfig} from "../../../../config/FrontendConfig";

export abstract class ConstraintValidator {
  abstract validatedConstraintName(): string;

  protected constructor(protected i18n: I18N) {
  }

  public addRule(rules: FluentRuleCustomizer<any, any>, metadata: Metadata, resource: Resource): FluentRuleCustomizer<any, any> {
    if (this.shouldValidate(metadata, resource)) {
      return rules
        .satisfies(value => this.validate(value, metadata, resource))
        .withMessage(this.getValidationErrorMessage(metadata, resource));
    }
  }

  private getValidationErrorMessage(metadata: Metadata, resource: Resource): string {
    const customMessageIdentifier = metadata.name + '_' + this.validatedConstraintName();
    const customMessage = this.i18n.tr('metadata_constraints::customMessages//' + FrontendConfig.get('theme')
      + '//' + customMessageIdentifier, {metadata, resource});
    if (customMessage.indexOf(customMessageIdentifier) > 0) {
      // console.log(customMessageIdentifier);
      return this.getErrorMessage(metadata, resource);
    } else {
      return customMessage;
    }
  }

  abstract getErrorMessage(metadata: Metadata, resource: Resource): string;

  protected shouldValidate(metadata: Metadata, resource: Resource): boolean {
    return true;
  }

  abstract validate(value, metadata: Metadata, resource: Resource): boolean | Promise<boolean>;
}

export abstract class SingleValueConstraintValidator extends ConstraintValidator {
  protected constructor(i18n: I18N) {
    super(i18n);
  }
}

export abstract class MetadataArrayConstraintValidator extends ConstraintValidator {
  abstract validate(values: any[], metadata: Metadata, resource: Resource): boolean | Promise<boolean>;
}
