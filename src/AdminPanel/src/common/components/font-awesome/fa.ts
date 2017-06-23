import {bindable, inlineView} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

@inlineView('<template><span class="fa ${fontAwesomeClasses}"></span></template>')
export class Fa {
  @bindable name: string;
  @bindable fw: boolean;

  @computedFrom('name', 'fw')
  get fontAwesomeClasses() {
    let classes = [this.name];
    if (this.fw) {
      classes.push('fw');
    }
    return classes.map(c => `fa-${c}`).join(' ');
  }
}
