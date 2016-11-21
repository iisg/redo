import {ValidationRules} from "aurelia-validation";

export class Language {
  flag: String = '';
  name: String = '';
}

ValidationRules
  .ensure('flag').displayName("Flaga").required()
  .ensure('name').displayName("Język").required()
  .on(Language);
