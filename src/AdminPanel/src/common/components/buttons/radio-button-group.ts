import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {generateId} from "common/utils/string-utils";

export class RadioButtonGroup {
  @bindable values: any[];
  @bindable(twoWay) checked: any;

  radioButtonName = generateId();
}
