import {Metadata} from "../../resources-config/metadata/metadata";
import {observable} from "aurelia-binding";
import {Resource} from "../resource";

export class ControlStrategy {
  metadata: Metadata;
  resource: Resource;
  resourceClass: string;
  @observable valueWrapper: ValueWrapper;
  disabled: boolean = false;

  activate(model: Object) {
    $.extend(this, model);
  }
}

export class ValueWrapper {
  value: any;
}
