import {bindable, ComponentDetached, ComponentAttached} from "aurelia-templating";
import {Metadata} from "../../resources-config/metadata/metadata";
import {bindingMode, BindingEngine, Disposable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {ValueWrapper} from "./controls/control-input";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourceMetadataValueInput implements ComponentAttached, ComponentDetached {
  @bindable metadata: Metadata;
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: any;

  valueWrapper: ValueWrapper = new ValueWrapper();
  subscription: Disposable;
  currentLanguageCode: string;

  constructor(private bindingEngine: BindingEngine, i18n: I18N) {
    this.currentLanguageCode = i18n.getLocale().toUpperCase();
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
