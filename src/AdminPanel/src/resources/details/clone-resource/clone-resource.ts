import {computedFrom} from "aurelia-binding";
import {SystemMetadata} from "../../../resources-config/metadata/system-metadata";
import {DisabilityReason} from "../../../common/components/buttons/toggle-button";
import {bindable} from "aurelia-templating";
import {Resource} from "../../resource";
import {autoinject} from "aurelia-dependency-injection";
import {HasRoleValueConverter} from "../../../common/authorization/has-role-value-converter";
import {Modal} from "../../../common/dialog/modal";
import {CloneResourceDialog, CloneResourceModel} from "./clone-resource-dialog";

@autoinject
export class CloneResource {
  @bindable parentResource: Resource;
  @bindable resource: Resource;
  @bindable clone: (value: {
    cloneTimes: number
  }) => Promise<any>;

  constructor(private hasRole: HasRoleValueConverter, private modal: Modal) {
  }

  @computedFrom('parentResource', 'resource')
  get cloningResourceDisabled(): boolean {
    return (this.parentResource && !this.allowAddSiblings())
      || (!this.parentResource && !this.hasRole.toView('ADMIN', this.resource.resourceClass));
  }

  private allowAddSiblings(): boolean {
    if (this.parentResource) {
      const parentMetadata = this.parentResource.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
      return !!parentMetadata.constraints.resourceKind.length;
    }
    return false;
  }

  get disabilityReason(): DisabilityReason {
    return {icon: 'help', message: 'Resource kind does not allow to add resource.'};
  }

  openCloneDialog() {
    const resourceLabel = this.resource.contents[SystemMetadata.RESOURCE_LABEL.id][0].value;
    this.modal.open(CloneResourceDialog, {label: resourceLabel} as CloneResourceModel)
      .then(cloneTimes => this.clone({cloneTimes}));
  }
}
