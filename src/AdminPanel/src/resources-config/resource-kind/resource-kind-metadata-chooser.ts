import {Metadata} from "../metadata/metadata";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceKind} from "./resource-kind";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class ResourceKindMetadataChooser {
  @bindable resourceKind: ResourceKind;
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;
}
