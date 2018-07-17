import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {AuditEntry} from "../audit-entry";

@autoinject
export class AuditEntryDetailsResource {
  @bindable entry: AuditEntry;
}
