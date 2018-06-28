import {computedFrom} from "aurelia-binding";
import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {changeHandler} from "common/components/binding-mode";
import {EntitySerializer} from "common/dto/entity-serializer";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {Metadata} from "./metadata";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";

@autoinject
export class MetadataForm extends ChangeLossPreventerForm implements ComponentAttached {
  @bindable submit: (value: { editedMetadata: Metadata }) => Promise<any>;
  @bindable cancel: () => void;
  @bindable(changeHandler('resetValues')) template: Metadata;
  @bindable edit: Metadata;
  @bindable resourceClass: string;
  controls: string[];
  submitting: boolean = false;
  metadata: Metadata = new Metadata();

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory,
              configuration: Configure,
              private entitySerializer: EntitySerializer,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
    this.controls = configuration.get('supported_controls');
  }

  @computedFrom('metadata.id')
  get fromTemplate(): boolean {
    return this.template != undefined;
  }

  attached(): void {
    this.changeLossPreventer.enable(this);
    this.resetValues();
  }

  cancelForm(): void {
    this.changeLossPreventer.canLeaveView().then(() => this.cancel());
  }

  private resetValues() {
    this.metadata = this.template ? this.entitySerializer.clone(this.template) : new Metadata();
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return this.submit({editedMetadata: this.metadata})
          .then(() => this.resetValues());  // resets values to updated ones provided via binding
      }
    }).finally(() => this.submitting = false);
  }
}
