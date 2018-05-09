import {Metadata} from "../metadata";
import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import * as changeCase from "change-case";

export class MetadataConstraint {
  @bindable name: string;
  @bindable metadata: Metadata;

  composeModel = {};

  attached(): void {
    this.composeModel = {
      metadata: this.metadata,
      constraintName: this.name
    };
  }

  @computedFrom('name')
  get constraintViewName(): string {
    return changeCase.paramCase(this.name);
  }
}
