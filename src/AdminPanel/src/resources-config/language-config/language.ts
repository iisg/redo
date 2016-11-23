import {ValidationRules} from "aurelia-validation";

export class Language {
  code: string = '';
  flag: string = '';
  name: string = '';
}

// ugly hack to disable the rules in the unit testing, see: https://github.com/aurelia/validation/issues/377#issuecomment-267791805
if ((ValidationRules as any).parser) {
  ValidationRules
    .ensure('code').displayName("Nazwa kodowa").required().matches(/^[A-Z-]*$/).withMessage('Tylko duże litery (A-Z) i myślnik "-"')
    .ensure('flag').displayName("Flaga").required()
    .ensure('name').displayName("Język").required()
    .on(Language);
}
