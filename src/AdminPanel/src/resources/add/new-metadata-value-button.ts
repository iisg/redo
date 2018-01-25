import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../resource";
import {computedFrom} from "aurelia-binding";
import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class NewMetadataValueButton {
  @bindable metadata: Metadata;
  @bindable resource: Resource;

  public constructor(private element: Element) {
  }

  @computedFrom('metadata.constraints.maxCount', 'values.length')
  get canAddMore() {
    return !this.metadata.constraints.maxCount || this.values.length < this.metadata.constraints.maxCount;
  }

  @computedFrom('resource.contents', 'metadata.id')
  get values() {
    return this.resource.contents[this.metadata.id];
  }

  addNew() {
    let event = DOM.createCustomEvent("add", {bubbles: true});
    this.element.dispatchEvent(event);
  }
}
