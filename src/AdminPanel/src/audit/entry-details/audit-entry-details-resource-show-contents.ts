import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Modal} from "common/dialog/modal";
import {Resource} from "../../resources/resource";
import {AuditEntry} from "../audit-entry";
import {AuditEntryDetailsResourceContentsModal} from "./audit-entry-details-resource-contents-modal";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";

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
    this.modal.open(AuditEntryDetailsResourceContentsModal, {
      resource: this.resource,
      entry: this.entry
    });
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
