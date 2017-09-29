import {bindable} from "aurelia-templating";
import {twoWay} from "../binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";

export class FabAdd {
  @bindable(twoWay) opened: boolean;
  @bindable @booleanAttribute disabled: boolean;
  @bindable @booleanAttribute turnIntoCross: boolean = true;
}
