import {ValidationRules} from "aurelia-validation";

export class Language {
  code: string = '';
  flag: string = '';
  name: string = '';
}

ValidationRules
  .ensure('code').displayName("Nazwa kodowa").required().matches(/^[A-Z-]*$/).withMessage('Tylko duże litery (A-Z) i myślnik "-"')
  .ensure('flag').displayName("Flaga").required()
  .ensure('name').displayName("Język").required()
  .on(Language);
