import {bindable} from "aurelia-templating";
import {Metadata} from "../metadata/metadata";
import {ValidationController} from "aurelia-validation";
import {ValidationControllerFactory} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceKindMetadataForm {
  @bindable metadata: Metadata;

  @bindable baseMetadata: Metadata;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
  }
}
