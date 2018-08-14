import {SingleValueConstraintValidator} from "./constraint-validator";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class NoOpenTreeConstraintValidator extends SingleValueConstraintValidator {
  validatedConstraintName(): string {
    return 'noOpenTree';
  }
  constructor(private i18n: I18N) {
    super();
  }

  validate(value: any, config: undefined): boolean {
    return value !== 'treeSelector';
  }

  getErrorMessage(value): string {
      return this.i18n.tr('metadata_constraints::Tree must be closed after selecting');
  }
}
