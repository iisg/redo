import {MultilingualText} from "resources-config/metadata/metadata";
import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {Entity} from "common/entity/entity";
import {automapped, map} from "common/dto/decorators";

@automapped
export class UserRole extends Entity {
  static NAME = 'UserRole';

  @map id: string;
  @map(Object.name) name: MultilingualText = {};
  @map systemRoleName: string;

  get systemRoleIdentifier(): string {
    return this.systemRoleName || this.id;
  }
}

export function registerUserRoleValidationRules() {
  ValidationRules
    .ensure('name').displayName('Name').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .on(UserRole);
}
