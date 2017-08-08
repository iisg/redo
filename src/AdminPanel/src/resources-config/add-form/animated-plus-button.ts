import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {booleanAttribute} from "../../common/components/boolean-attribute";

export class AnimatedPlusButton {
  @bindable({defaultBindingMode: bindingMode.twoWay}) opened: boolean;
  @bindable @booleanAttribute disabled: boolean;
}
