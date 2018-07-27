import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {values} from "lodash";
import {MetadataControl} from "./metadata-control";

export class MetadataControlSelect {
  @bindable(twoWay) value: string;
  @bindable disabled: boolean;

  controls: string[] = values(MetadataControl);

  dropdown: Element;
}
