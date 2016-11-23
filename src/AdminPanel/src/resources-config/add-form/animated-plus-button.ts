import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";

export class AnimatedPlusButton {
  @bindable({defaultBindingMode: bindingMode.twoWay}) opened: boolean;
}
