import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {ChangeLossPreventer} from "common/change-loss-preventer/change-loss-preventer";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ChangeLossPreventerForm} from "common/form/change-loss-preventer-form";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {values} from "lodash";
import {Metadata} from "./metadata";
import {MetadataControl} from "./metadata-control";

@autoinject
export class MetadataForm extends ChangeLossPreventerForm implements ComponentAttached {
  @bindable submit: (value: { editedMetadata: Metadata }) => Promise<any>;
  @bindable cancel: () => void;
  @bindable template: Metadata;
  @bindable currentlyEditedMetadata: Metadata;
  @bindable resourceClass: string;
  @bindable hideMetadataGroupChooser: boolean = false;
  controls: string[] = values(MetadataControl);
  submitting: boolean = false;
  metadata: Metadata = new Metadata();
  shouldBeDynamic: boolean = false;

  validationController: ValidationController;
  private restoredPreviousTemplateValue = false;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private entitySerializer: EntitySerializer,
              public changeLossPreventer: ChangeLossPreventer) {
    super();
    this.validationController = validationControllerFactory.createForCurrentScope();
    this.validationController.addRenderer(new BootstrapValidationRenderer);
  }

  attached(): void {
    this.resetValues();
  }

  templateChanged(newValue: Metadata, oldValue: Metadata) {
    if (!this.restoredPreviousTemplateValue) {
      this.changeLossPreventer.canLeaveView().then(canLeaveView => {
        if (canLeaveView) {
          this.resetValues();
        } else {
          this.restoredPreviousTemplateValue = true;
          this.template = oldValue;
        }
      });
    } else {
      this.restoredPreviousTemplateValue = false;
    }
  }

  @computedFrom('metadata.id')
  get fromTemplate(): boolean {
    return this.template != undefined;
  }

  cancelForm(): void {
    this.changeLossPreventer.canLeaveView().then(canLeaveView => {
      if (canLeaveView) {
        this.cancel();
      }
    });
  }

  private resetValues() {
    this.changeLossPreventer.enable(this);
    this.metadata = this.template ? this.entitySerializer.clone(this.template) : new Metadata();
    if (this.metadata.id) {
      this.shouldBeDynamic = this.metadata.isDynamic;
    }
  }

  validateAndSubmit() {
    if (!this.metadata.id && !this.shouldBeDynamic) {
      this.metadata.displayStrategy = undefined;
    }
    this.submitting = true;
    this.validationController.validate().then(result => {
      if (result.valid) {
        return this.submit({editedMetadata: this.metadata})
          .then(() => this.resetValues());  // resets values to updated ones provided via binding
      }
    }).finally(() => this.submitting = false);
  }
}
