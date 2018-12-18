import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../../../resource";
import {ValidationController} from "aurelia-validation";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class TreeRelationshipSelector {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable disabled: boolean = false;
  @bindable skipValidation: boolean = false;
  @bindable validationController: ValidationController;
  @bindable showByDefault: boolean = false;
  @bindable treeQueryUrl: string;
  loaded: boolean = false;
  shown: boolean = false;

  attached() {
    if (this.showByDefault) {
      this.loaded = true;
      this.shown = true;
    }
  }

  showTree() {
    this.loaded = true;
    this.shown = true;
  }
}
