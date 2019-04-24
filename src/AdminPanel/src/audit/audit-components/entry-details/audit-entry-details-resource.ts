import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {AuditEntry} from "../audit-entry";
import {
  AuditEntryDetailsResourceContentsDialog,
  AuditEntryDetailsResourceContentsDialogModel
} from "./audit-entry-details-resource-contents-dialog";
import {Modal} from "common/dialog/modal";
import {Resource} from "resources/resource";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";

@autoinject
export class AuditEntryDetailsResource {
  @bindable entry: AuditEntry;
  beforeChangeResource: Resource;
  afterChangeResource: Resource;

  constructor(private modal: Modal) {
  }

  async attached() {
    this.beforeChangeResource = this.entry.data['before'] ? this.setResource(this.entry.data['before'].resource) : undefined;
    this.afterChangeResource = this.entry.data['after'] ? this.setResource(this.entry.data['after'].resource) : undefined;
  }

  showResourceContentsDialog() {
    this.modal.open(AuditEntryDetailsResourceContentsDialog, {
      beforeChangeResource: this.beforeChangeResource,
      afterChangeResource: this.afterChangeResource,
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
