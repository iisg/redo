import {bindable, inlineView} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {inArray, unique} from "common/utils/array-utils";
import {changeHandler} from "../binding-mode";

@inlineView('<template><span class="fa ${classes}"></span></template>')
export class Fa {
  @bindable(changeHandler('updateClasses')) name: string;
  @bindable(changeHandler('updateClasses')) options: string;

  readonly AVAILABLE_OPTIONS: string[] =
    'lg 2x 3x 4x 5x quote-left pull-left border fw spin pulse flip-horizontal flip-vertical rotate-90 rotate-180 rotate-270'.split(/ +/g);

  private removePrefix(str: string): string {
    return str.replace(/^fa-/, '');
  }

  private processValues(str: string): string[] {
    return (str || '').split(/\s+/g).filter(s => s.length > 0).map(this.removePrefix);
  }

  get nameArray(): string[] {
    return this.processValues(this.name);
  }

  get optionsArray(): string[] {
    return this.processValues(this.options);
  }

  @computedFrom('name', 'options')
  get classes(): string {
    const name = this.nameArray[0] || '';
    const nameOptions = this.nameArray.slice(1);
    const options = unique(nameOptions.concat(this.optionsArray));
    for (const option of options) {
      if (!inArray(option, this.AVAILABLE_OPTIONS)) {
        throw new Error(`Unknown FontAwesome option '${option}'`);
      }
    }
    return [name].concat(options).map(c => `fa-${c}`).join(' ');
  }
}
