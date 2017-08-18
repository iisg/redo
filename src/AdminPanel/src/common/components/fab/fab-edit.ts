import {booleanAttribute} from "../boolean-attribute";
import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {noop, VoidFunction} from "common/utils/function-utils";

export class FabEdit {
  @bindable @booleanAttribute editing: boolean = false;
  @bindable @booleanAttribute shiftDown: boolean = false;
  @bindable onClick: VoidFunction = noop;
  @bindable @booleanAttribute disabled: boolean;

  click(): void {
    if (!this.disabled) {
      this.onClick();
    }
  }

  @computedFrom('editing', 'shiftDown', 'disabled')
  get classes(): string {
    let classes: string[] = this.editing ? ['btn-warning', 'editing'] : ['btn-info'];
    if (this.shiftDown) {
      classes.push('shifted');
    }
    if (this.disabled) {
      classes.push('disabled');
    }
    return classes.join(' ');
  }
}
