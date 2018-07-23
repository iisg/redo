import {bindable, ComponentAttached} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Language} from "./language";
import {noop, VoidFunction} from "common/utils/function-utils";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";

@autoinject
export class LanguageForm extends ChangeLossPreventerForm implements ComponentAttached {
  @bindable submit: (value: {savedLanguage: Language}) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable edit: Language;

  editing: boolean = false;

  language: Language = new Language();
  submitting: boolean = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private entitySerializer: EntitySerializer,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  attached(): void {
    this.changeLossPreventer.enable(this);
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
          .then(() => this.changeLossPreventer.enable(this))
          .then(() => this.editing || (this.language = new Language));
      }
    }).finally(() => this.submitting = false);
  }
}
