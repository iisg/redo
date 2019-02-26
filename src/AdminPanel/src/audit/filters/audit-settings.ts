import {map} from "common/dto/decorators";
import {autoinject} from "aurelia-dependency-injection";
import {MultilingualText} from "../../resources-config/metadata/metadata";

@autoinject
export class AuditSettings {
  @map id: string;
  @map url: string;
  @map label: MultilingualText;

  constructor(initialValues?: AuditSettings) {
    $.extend(this, initialValues);
  }
}
