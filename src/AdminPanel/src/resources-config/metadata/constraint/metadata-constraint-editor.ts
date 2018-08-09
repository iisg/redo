import {computedFrom} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {ValidationController} from "aurelia-validation";
import * as changeCase from "change-case";
import {oneTime} from "common/components/binding-mode";
import {Metadata} from "../metadata";

export class MetadataConstraintEditor {
  @bindable(oneTime) name: string;
  @bindable metadata: Metadata;
  @bindable originalMetadata: Metadata;
  @bindable validationController: ValidationController;

  composeModel = {};

  attached(): void {
    this.composeModel = {
      metadata: this.metadata,
      originalMetadata: this.originalMetadata,
      metadataName: this.name
    };
  }

  @computedFrom('name')
  get editorViewName(): string {
    return changeCase.paramCase(this.name);
  }
}
