import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceMetadataValuesForm {
  @bindable metadata: Metadata;
  @bindable(twoWay) resource: Resource;
  @bindable disabled: boolean = false;

  valueTable: Element;

  metadataChanged() {
    if (this.resource != undefined) {
      this.ensureResourceHasMetadataContents();
    }
  }

  resourceChanged() {
    if (this.metadata != undefined) {
      this.ensureResourceHasMetadataContents();
    }
  }

  private ensureResourceHasMetadataContents() {
    const contents = this.resource.contents;
    const baseId = this.metadata.baseId;
    if (!contents.hasOwnProperty(baseId)) {
      contents[baseId] = [];
    }
  }

  deleteIndex(index: number) {
    if (this.disabled) {
      return;
    }
    this.resource.contents[this.metadata.baseId].splice(index, 1);
  }

  addNew() {
    this.resource.contents[this.metadata.baseId].push(undefined);
    // queueMicroTask and queueTask fire too early and the <input> doesn't exist yet.
    // setTimeout(..., 0) fires at right time, but something steals the focus later.
    // setTimeout + queue[Micro]Task isn't reliable, it works for second and subsequent inputs but not first one
    setTimeout(() => {
      $(this.valueTable).find('td.content').last().find('input, textarea').first().focus();
    }, 50);
  }
}
