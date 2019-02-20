import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../../../resource";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class ResourceRelationshipSelector {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable disabled: boolean = false;
  @bindable skipValidation: boolean = false;
  @bindable showByDefault: boolean = false;
  loaded: boolean = false;
  shown: boolean = false;

  attached() {
    let selectorType = this.metadata.constraints.relationshipSelectorType;
    if (this.showByDefault) {
      this.loaded = true;
      this.shown = true;
    }
  }

  showSelector() {
    this.loaded = true;
    this.shown = true;
  }
}
