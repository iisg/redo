import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {BindingEngine} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {ValueWrapper} from "common/utils/value-wrapper";
import {booleanAttribute} from "common/components/boolean-attribute";
import {twoWay} from "common/components/binding-mode";
import {BindingSignaler} from "aurelia-templating-resources";
import {Resource} from "../resource";
import {ValidationController} from "aurelia-validation";

@autoinject
export class ResourceMetadataValueInput implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable(twoWay) value: any;
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable validationController: ValidationController;

  valueWrapper: ValueWrapper<any> = new ValueWrapper();

  constructor(private bindingEngine: BindingEngine, private bindingSignaler: BindingSignaler) {
  }

  attached() {
    this.valueWrapper.onChange(this.bindingEngine, () => this.wrappedValueChanged());
  }

  detached() {
    this.valueWrapper.cancelChangeSubscriptions();
  }

  valueChanged() {
    this.valueWrapper.value = this.value;
    this.sendChangeSignal();
  }

  wrappedValueChanged() {
    this.value = this.valueWrapper.value;
    this.sendChangeSignal();
  }

  private sendChangeSignal(): void {
    this.bindingSignaler.signal('metadata-values-changed');
  }
}
