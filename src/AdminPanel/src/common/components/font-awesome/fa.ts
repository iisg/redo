import {bindable, inlineView} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

@inlineView('<template><span class="fa ${fontAwesomeClasses}"></span></template>')
export class Fa {
  @bindable name: string;
  @bindable fw: boolean;
  @bindable spin: boolean;
  @bindable flipH: boolean;

  readonly REPLACEMENTS: StringMap<string> = {
    'flipH': 'flip-horizontal',
  };

  @computedFrom('name', 'fw')
  get fontAwesomeClasses() {
    let classes = [this.name].concat(this.getBooleanClasses());
    return classes.map(c => `fa-${c}`).join(' ');
  }

  private getBooleanClasses(): string[] {
    return ['fw', 'spin', 'flipH']
      .filter(propertyName => this[propertyName] !== undefined) // careful: <fa fw> sets fw to '' which is falsy
      .map(propertyName => this.REPLACEMENTS.hasOwnProperty(propertyName) ? this.REPLACEMENTS[propertyName] : propertyName);
  }
}
