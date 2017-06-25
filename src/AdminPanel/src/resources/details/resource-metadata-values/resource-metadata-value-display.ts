import {ComponentAttached, ComponentDetached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {ValueWrapper} from "../../controls/control-strategy";
import {Disposable, BindingEngine} from "aurelia-binding";

@autoinject
export class ResourceMetadataValueDisplay implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable value: any;

  valueWrapper: ValueWrapper = new ValueWrapper();
  subscription: Disposable;

  constructor(private bindingEngine: BindingEngine) {
  }

  attached(): void {
    this.subscription = this.bindingEngine
      .propertyObserver(this.valueWrapper, 'value')
      .subscribe(() => this.wrappedValueChanged());
  }

  detached(): void {
    this.subscription.dispose();
  }

  valueChanged() {
    this.valueWrapper.value = this.value;
  }

  wrappedValueChanged() {
    this.value = this.valueWrapper.value;
  }
}
