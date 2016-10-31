import {CustomValidationRule} from "../custom-validation-rules";
import {autoinject} from "aurelia-dependency-injection";
import {Configure} from "aurelia-configuration";

@autoinject
export class RequiredInMainLanguageValidationRule implements CustomValidationRule {
  static readonly NAME: string = RequiredInMainLanguageValidationRule.name;

  private readonly mainLanguage;

  constructor(private config: Configure) {
    this.mainLanguage = config.get('supported_languages')[0];
  }

  name(): string {
    return RequiredInMainLanguageValidationRule.NAME;
  }

  message(): string {
    return `\${$displayName} musi posiadać wartość w głównym języku (${this.mainLanguage}).`;
  }

  validationFunction(): (object) => boolean {
    return (value) => typeof value != 'object' || (value[this.mainLanguage] && value[this.mainLanguage].trim());
  }
}
