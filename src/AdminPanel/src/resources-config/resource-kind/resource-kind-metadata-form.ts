import {bindable, useView} from "aurelia-templating";
import {Metadata} from "../metadata/metadata";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
@useView("../metadata/metadata-editable-properties.html")
export class ResourceKindMetadataForm {
  @bindable metadata: Metadata;
  @bindable originalMetadata: Metadata;
  @bindable editing: boolean = false;
  @bindable resourceClass: string;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
  }
}
