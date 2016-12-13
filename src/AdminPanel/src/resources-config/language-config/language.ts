import {ValidationRules} from "aurelia-validation";

export class Language {
  code: string = '';
  flag: string = '';
  name: string = '';
}

// ugly hack to disable the rules in the unit testing, see: https://github.com/aurelia/validation/issues/377#issuecomment-267791805
if ((ValidationRules as any).parser) {
  ValidationRules
    .ensure('code').displayName("Code").required().matches(/^[A-Z]+(?:-[A-Z])*$/).withMessageKey('isLanguageCode')
    .ensure('flag').displayName("Flag").required()
    .ensure('name').displayName("Language").required()
    .on(Language);
}
