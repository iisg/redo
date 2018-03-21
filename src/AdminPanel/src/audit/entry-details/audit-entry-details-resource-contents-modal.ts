import {Resource} from "../../resources/resource";
import {AuditEntry} from "../audit-entry";
import {DialogController} from "aurelia-dialog";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class AuditEntryDetailsResourceContentsModal {
  resource: Resource;
  entry: AuditEntry;

  constructor(private dialogController: DialogController) {
  }

  activate(model) {
    this.resource = model.resource;
    this.entry = model.entry;
  }
}
