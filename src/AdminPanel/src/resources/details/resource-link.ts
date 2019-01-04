import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceLink {
  @bindable id: number;
  @bindable resource: Resource;
}
