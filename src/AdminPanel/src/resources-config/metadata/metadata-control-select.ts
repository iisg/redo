import {bindable} from "aurelia-templating";
import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class MetadataControlSelect {
  @bindable(twoWay) value: string;
  @bindable disabled: boolean;

  controls: string[];

  dropdown: Element;

  constructor(config: Configure) {
    this.controls = config.get('supported_controls');
  }
}
