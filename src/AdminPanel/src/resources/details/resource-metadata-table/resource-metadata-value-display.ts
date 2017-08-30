import {ComponentAttached, ComponentDetached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {BindingEngine} from "aurelia-binding";
import {Resource} from "../../resource";
import {ValueWrapper} from "common/utils/value-wrapper";

@autoinject
export class ResourceMetadataValueDisplay implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable value: any;

  valueWrapper: ValueWrapper<any> = new ValueWrapper();

  constructor(private bindingEngine: BindingEngine) {
  }

  attached(): void {
    this.valueWrapper.onChange(this.bindingEngine, () => this.wrappedValueChanged());
  }

  detached(): void {
    this.valueWrapper.cancelChangeSubscriptions();
  }

  valueChanged() {
    this.valueWrapper.value = this.value;
  }

  wrappedValueChanged() {
    this.value = this.valueWrapper.value;
  }
}
