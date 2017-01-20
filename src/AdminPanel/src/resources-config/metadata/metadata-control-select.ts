import {bindable} from "aurelia-templating";
import {Configure} from "aurelia-configuration";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class MetadataControlSelect {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: string;
  @bindable disabled: boolean;

  controls: string[];

  dropdown: Element;

  constructor(config: Configure) {
    this.controls = config.get('supported_controls');
  }
}
