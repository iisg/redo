import {bindable} from "aurelia-templating";
import {WorkflowPluginConfigurationOption} from "./workflow-plugin";
import {MetadataValue} from "../../../resources/metadata-value";
import {BindingEngine} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "../../../common/components/binding-mode";

@autoinject
export class WorkflowPluginConfigurationOptionInput {
  @bindable option: WorkflowPluginConfigurationOption;
  @bindable(twoWay) value;
  @bindable description: string;

  private metadataValue: MetadataValue = new MetadataValue();

  constructor(private bindingEngine: BindingEngine) {
  }

  attached() {
    this.metadataValue.onChange(this.bindingEngine, () => this.onValueChange());
  }

  detached() {
    this.metadataValue.clearChangeListener();
  }

  valueChanged() {
    this.metadataValue.value = this.value;
  }

  private onValueChange() {
    this.value = this.metadataValue.value;
  }
}
