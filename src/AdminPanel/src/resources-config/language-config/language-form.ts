import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Language} from "./language";
import {noop, VoidFunction} from "common/utils/function-utils";
import {EntitySerializer} from "common/dto/entity-serializer";

@autoinject
export class LanguageForm {
  @bindable submit: (value: {savedLanguage: Language}) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable edit: Language;
  editing: boolean = false;

  language: Language = new Language;
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory, private entitySerializer: EntitySerializer) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  editChanged(newValue: Language) {
    this.editing = !!newValue;
    this.language = this.entitySerializer.clone(newValue);
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
