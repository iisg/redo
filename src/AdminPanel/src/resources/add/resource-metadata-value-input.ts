import {bindable, ComponentDetached, ComponentAttached} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {BindingEngine, Disposable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {ValueWrapper} from "../controls/control-strategy";
import {booleanAttribute} from "common/components/boolean-attribute";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceMetadataValueInput implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable(twoWay) value: any;
  @bindable @booleanAttribute disabled: boolean = false;

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
}
