import {autoinject} from "aurelia-dependency-injection";
import {bindable, useView} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {Metadata} from "../metadata/metadata";

@autoinject
@useView("../metadata/metadata-editable-properties.html")
export class ResourceKindMetadataForm {
  @bindable metadata: Metadata;
  @bindable originalMetadata: Metadata;
  @bindable editing: boolean = false;
  @bindable resourceClass: string;
  validationControllerForCurrentScope: ValidationController;

  constructor(public validationController: ValidationController, validationControllerFactory: ValidationControllerFactory) {
    this.validationControllerForCurrentScope = validationControllerFactory.createForCurrentScope();
  }
}
