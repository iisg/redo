import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Modal} from "common/dialog/modal";
import {ResourceKindRepository} from "../../resources-config/resource-kind/resource-kind-repository";
import {Resource} from "../../resources/resource";
import {AuditEntry} from "../audit-entry";
import {AuditEntryDetailsResourceContentsModal} from "./audit-entry-details-resource-contents-modal";

@autoinject
export class AuditEntryDetailsResource {
  @bindable entry: AuditEntry;
  resource: Resource = new Resource();

  constructor(private resourceKindRepository: ResourceKindRepository, private modal: Modal) {
  }

  async attached() {
    let resource = this.entry.data.resource;
    if (resource) {
      this.resource.id = resource.id;
      this.resource.resourceClass = resource.resourceClass;
      this.resource.contents = resource.contents;
      this.resource.kind = await this.resourceKindRepository.get(resource.kindId);
    }
  }

  showResourceContentsDialog() {
    this.modal.open(AuditEntryDetailsResourceContentsModal, {resource: this.resource, entry: this.entry});
  }
}
