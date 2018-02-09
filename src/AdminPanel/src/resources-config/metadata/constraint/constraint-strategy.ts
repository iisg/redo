import {ValueWrapper} from "common/utils/value-wrapper";
import {Metadata} from "../metadata";

export class ConstraintStrategy {
  metadataWrapper: ValueWrapper<Metadata>;
  originalMetadataWrapper: ValueWrapper<Metadata>;
  constraintName: string;

  activate(model: Object) {
    $.extend(this, model);
  }
}
