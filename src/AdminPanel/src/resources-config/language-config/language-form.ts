import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Language} from "./language";
import {deepCopy} from "common/utils/object-utils";

@autoinject
export class LanguageForm {
  @bindable submit: (value: {savedLanguage: Language}) => Promise<any>;
  @bindable cancel: () => any = () => undefined;
  @bindable edit: Language;
  editing: boolean = false;

  language: Language = new Language;
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  editChanged(newValue: Language) {
    this.editing = !!newValue;
    this.language = $.extend(new Language(), deepCopy(newValue));
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({savedLanguage: this.language}))
          .then(() => this.editing || (this.language = new Language));
      }
    }).finally(() => this.submitting = false);
  }
}
