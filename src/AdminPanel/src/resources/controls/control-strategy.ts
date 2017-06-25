import {Metadata} from "../../resources-config/metadata/metadata";
import {observable} from "aurelia-binding";

export class ControlStrategy {
  metadata: Metadata;
  @observable valueWrapper: ValueWrapper;

  activate(model: Object) {
    $.extend(this, model);
  }
}

export class ValueWrapper {
  value: any;
}
