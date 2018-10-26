import {bindable} from 'aurelia-templating';
import {computedFrom} from "aurelia-binding";

export class Icon {
  @bindable name: string;
  @bindable rotation: number;
  @bindable size = 1;
  path = "/files/icons.svg";

  @computedFrom('size')
  get escapedSize() {
    return ('' + this.size).replace('.', '_');
  }
}
