import {booleanAttribute} from "../boolean-attribute";
import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

export class FabEdit {
  @bindable @booleanAttribute editing: boolean = false;
  @bindable @booleanAttribute shiftDown: boolean = false;
  @bindable @booleanAttribute disabled: boolean;

  @computedFrom('editing', 'shiftDown', 'disabled')
  get classes(): string {
    let classes: string[] = this.editing ? ['btn-warning', 'editing'] : ['btn-info'];
    if (this.shiftDown) {
      classes.push('shifted');
    }
    return classes.join(' ');
  }
}
