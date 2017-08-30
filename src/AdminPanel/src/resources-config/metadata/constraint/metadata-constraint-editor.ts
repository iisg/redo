import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {BindingEngine, computedFrom} from "aurelia-binding";
import {ValueWrapper} from "common/utils/value-wrapper";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../metadata";
import * as changeCase from "change-case";
import {twoWay, oneTime} from "common/components/binding-mode";

@autoinject
export class MetadataConstraintEditor implements ComponentAttached, ComponentDetached {
  @bindable(oneTime) name: string;
  @bindable(twoWay) metadata: Metadata;
  @bindable(oneTime) baseMetadata: Metadata;
  @bindable(oneTime) resourceClass: string;

  readonly metadataWrapper: ValueWrapper<Metadata> = new ValueWrapper<Metadata>();
  readonly baseMetadataWrapper: ValueWrapper<Metadata> = new ValueWrapper<Metadata>();

  composeModel = {};

  constructor(private bindingEngine: BindingEngine) {
  }

  attached(): void {
    this.metadataWrapper.onChange(this.bindingEngine, () => this.wrappedValueChanged());
    this.baseMetadataWrapper.onChange(this.bindingEngine, () => this.wrappedBaseValueChanged());

    this.composeModel = {
      metadataWrapper: this.metadataWrapper,
      baseMetadataWrapper: this.baseMetadataWrapper,
      metadataName: this.name,
      resourceClass: this.resourceClass
    };
  }

  detached(): void {
    this.metadataWrapper.cancelChangeSubscriptions();
    this.baseMetadataWrapper.cancelChangeSubscriptions();
  }

  metadataChanged() {
    this.metadataWrapper.value = this.metadata;
  }

  wrappedValueChanged() {
    this.metadata = this.metadataWrapper.value;
  }

  baseMetadataChanged() {
    this.baseMetadataWrapper.value = this.baseMetadata;
  }

  wrappedBaseValueChanged() {
    this.baseMetadata = this.baseMetadataWrapper.value;
  }

  @computedFrom('name')
  get editorViewName(): string {
    return changeCase.paramCase(this.name);
  }
}
