import {bindable, ComponentDetached, ComponentAttached} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {bindingMode, BindingEngine, Disposable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {ValueWrapper} from "../controls/control-strategy";

@autoinject
export class ResourceMetadataValueInput implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: any;
  @bindable disabled: boolean = false;

  valueWrapper: ValueWrapper = new ValueWrapper();
  subscription: Disposable;

  constructor(private bindingEngine: BindingEngine) {
  }

  attached() {
    this.subscription = this.bindingEngine
      .propertyObserver(this.valueWrapper, 'value')
      .subscribe(() => this.wrappedValueChanged());
  }

  detached() {
    this.subscription.dispose();
  }

  valueChanged() {
    this.valueWrapper.value = this.value;
  }

  wrappedValueChanged() {
    this.value = this.valueWrapper.value;
  }

  disabledChanged() {
    if (this.disabled as any === '') { // when used without value: <resource-metadata-value-input disabled>
      this.disabled = true;
    }
  }
}
