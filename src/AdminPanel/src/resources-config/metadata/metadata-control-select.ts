import {bindable, ComponentAttached} from "aurelia-templating";
import {Configure} from "aurelia-configuration";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class MetadataControlSelect implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: string;

  controls: string[];

  dropdown: Element;

  constructor(config: Configure) {
    this.controls = config.get('supported_controls');
  }

  attached() {
    $(this.dropdown).selectpicker();
  }
}
