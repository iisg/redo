import {bindable} from "aurelia-templating";
import {Resource} from "./resource";
import {oneTime} from "common/components/binding-mode";
import {Metadata} from "../resources-config/metadata/metadata";

export class ResourcesListTable {
  @bindable resources: Resource[];
  @bindable columnMetadata: Metadata[];
  @bindable(oneTime) deleteResource: (value: {resource: Resource}) => void;
  @bindable(oneTime) extraColumnNames: string[] = [];
  @bindable(oneTime) extraColumnViews: string[] = [];
}
