import {ValidationRules} from "aurelia-validation";
import {Entity} from "common/entity/entity";

export class Language extends Entity {
  code: string = '';
  flag: string = '';
  name: string = '';
}

export function registerLanguageValidationRules() {
  ValidationRules
    .ensure('code').displayName("Code").required().matches(/^[A-Z]+-?[A-Z]*[A-Z]$/).withMessageKey('isLanguageCode')
    .ensure('code').displayName("Code").required().maxLength(10).withMessageKey('lengthUpTo10')
    .ensure('flag').displayName("Flag").required()
    .ensure('name').displayName("Language").required()
    .on(Language);
}
