import {MultilingualText} from "resources-config/metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Entity} from "../../common/entity/entity";

export class UserRole extends Entity {
  id: string;
  name: MultilingualText = {};
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
