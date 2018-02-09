import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {BindingEngine, computedFrom} from "aurelia-binding";
import {ValueWrapper} from "common/utils/value-wrapper";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../metadata";
import * as changeCase from "change-case";
import {oneTime, twoWay} from "common/components/binding-mode";

@autoinject
export class MetadataConstraintEditor implements ComponentAttached, ComponentDetached {
  @bindable(oneTime) name: string;
  @bindable(twoWay) metadata: Metadata;
  @bindable(oneTime) originalMetadata: Metadata;
  @bindable(oneTime) resourceClass: string;

  readonly metadataWrapper: ValueWrapper<Metadata> = new ValueWrapper<Metadata>();
  readonly originalMetadataWrapper: ValueWrapper<Metadata> = new ValueWrapper<Metadata>();

  composeModel = {};

  constructor(private bindingEngine: BindingEngine) {
  }

  attached(): void {
    this.metadataWrapper.onChange(this.bindingEngine, () => this.wrappedValueChanged());
    this.originalMetadataWrapper.onChange(this.bindingEngine, () => this.wrappedOriginalValueChanged());

    this.composeModel = {
      metadataWrapper: this.metadataWrapper,
      originalMetadataWrapper: this.originalMetadataWrapper,
      metadataName: this.name,
      resourceClass: this.resourceClass
    };
  }

  detached(): void {
    this.metadataWrapper.cancelChangeSubscriptions();
    this.originalMetadataWrapper.cancelChangeSubscriptions();
  }

  metadataChanged() {
    this.metadataWrapper.value = this.metadata;
  }

  wrappedValueChanged() {
    this.metadata = this.metadataWrapper.value;
  }

  originalMetadataChanged() {
    this.originalMetadataWrapper.value = this.originalMetadata;
  }

  wrappedOriginalValueChanged() {
    this.originalMetadata = this.originalMetadataWrapper.value;
  }

  @computedFrom('name')
  get editorViewName(): string {
    return changeCase.paramCase(this.name);
  }
}
