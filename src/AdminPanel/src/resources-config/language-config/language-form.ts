import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {Language} from "./language";

@autoinject
export class LanguageForm {
  language: Language = new Language;

  @bindable
  submit: (value: {language: Language}) => Promise<any>;

  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({language: this.language}))
          .then(() => this.language = new Language);
      }
    }).finally(() => this.submitting = false);
  }
}
