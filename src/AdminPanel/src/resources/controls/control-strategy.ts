import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {ValueWrapper} from "common/utils/value-wrapper";

export class ControlStrategy {
  metadata: Metadata;
  resource: Resource;
  valueWrapper: ValueWrapper<any>;
  disabled: boolean = false;

  activate(model: Object) {
    $.extend(this, model);
  }
}
