import {MultilingualTextType} from "../../resources-config/metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "../../common/validation/rules/required-in-all-languages";

export class UserRole {
  id: string;
  name: MultilingualTextType = {};
  systemRoleName: string;

  get systemRoleIdentifier(): string {
    return this.systemRoleName || this.id;
  }
}

export function registerUserRoleValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .on(UserRole);
}
