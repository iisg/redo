import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {booleanAttribute} from "common/components/boolean-attribute";

export class FabAdd {
  @bindable({defaultBindingMode: bindingMode.twoWay}) opened: boolean;
  @bindable @booleanAttribute disabled: boolean;
}
