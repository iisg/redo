import {bindable} from "aurelia-templating";
import {Metadata} from "../../resources-config/metadata/metadata";
import {Resource} from "../resource";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceMetadataValuesForm {
  @bindable metadata: Metadata;
  @bindable({defaultBindingMode: bindingMode.twoWay}) resource: Resource;

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
    if (!this.resource.contents.hasOwnProperty(this.metadata.baseId)) {
      this.resource.contents[this.metadata.baseId] = [];
    }
  }

  deleteIndex(index: number) {
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
