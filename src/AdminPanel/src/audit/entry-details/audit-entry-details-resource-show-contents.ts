import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Modal} from "common/dialog/modal";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {Resource} from "../../resources/resource";
import {AuditEntry} from "../audit-entry";
import {AuditEntryDetailsResourceContentsDialog, AuditEntryDetailsResourceContentsDialogModel}
  from "./audit-entry-details-resource-contents-dialog";

@autoinject
export class AuditEntryDetailsResourceShowContents {
  @bindable entry: AuditEntry;
  @bindable dataKey: string;
  resource: Resource;

  constructor(private modal: Modal) {
  }

  async attached() {
    this.resource = this.setResource(this.entry.data[this.dataKey].resource);
  }

  showResourceContentsDialog() {
    this.modal.open(AuditEntryDetailsResourceContentsDialog, {
      resource: this.resource,
      entry: this.entry
    } as AuditEntryDetailsResourceContentsDialogModel);
  }

  private setResource(resourceData): Resource {
    const resource = new Resource();
    resource.id = resourceData.id;
    resource.resourceClass = resourceData.resourceClass;
    resource.contents = resourceData.contents;
    resource.kind = new ResourceKind();
    resource.kind.id = resourceData.kindId;
    return resource;
  }
}
