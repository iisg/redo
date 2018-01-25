import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../metadata";
import * as changeCase from "change-case";
import {oneTime} from "common/components/binding-mode";

@autoinject
export class MetadataConstraintEditor {
  @bindable(oneTime) name: string;
  @bindable metadata: Metadata;
  @bindable originalMetadata: Metadata;

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
