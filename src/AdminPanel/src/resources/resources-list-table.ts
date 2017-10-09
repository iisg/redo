import {bindable} from "aurelia-templating";
import {Resource} from "./resource";
import {oneTime} from "common/components/binding-mode";

export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable(oneTime) deleteResource: (value: {resource: Resource}) => void;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
}
