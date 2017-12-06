import {ValidationRules} from "aurelia-validation";
import {Entity} from "common/entity/entity";
import {automapped, map} from "common/dto/decorators";

@automapped
export class Language extends Entity {
  static NAME = 'Language';

  @map code: string = '';
  @map flag: string = '';
  @map name: string = '';
}

export function registerLanguageValidationRules() {
  ValidationRules
    .ensure('code').displayName("Code").required().matches(/^[A-Z]+-?[A-Z]*[A-Z]$/).withMessageKey('isLanguageCode')
    .ensure('code').displayName("Code").required().maxLength(10).withMessageKey('lengthUpTo10')
    .ensure('flag').displayName("Flag").required()
    .ensure('name').displayName("Language").required()
    .on(Language);
}
