import {bindable, inlineView} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

@inlineView('<template><span class="fa ${fontAwesomeClasses}"></span></template>')
export class Fa {
  @bindable name: string;
  @bindable options: string;

  readonly AVAILABLE_OPTIONS: string[] = `lg 2x 3x 4x 5x quote-left pull-left border fw spin pulse
                                          flip-horizontal flip-vertical rotate-90 rotate-180 rotate-270`.split(/ +/g);

  nameChanged(newName: string): void {
    const parts = newName.split(/ +/g).map(this.removePrefix);
    this.name = parts.shift();
    this.setOptions(parts);
  }

  optionsChanges(newOptions: string): void {
    const options = newOptions.split(/ +/g).map(this.removePrefix);
    this.setOptions(options);
  }

  private removePrefix(str: string): string {
    return str.replace(/^fa-/, '');
  }

  private setOptions(options: string[]) {
    for (const option of this.AVAILABLE_OPTIONS) {
      this[option] = (options.indexOf(option) != -1);
    }
  }

  @computedFrom('name', 'options')
  get fontAwesomeClasses() {
    let classes = [this.name].concat(this.getBooleanClasses());
    return classes.map(c => `fa-${c}`).join(' ');
  }

  private getBooleanClasses(): string[] {
    return this.AVAILABLE_OPTIONS.filter(propertyName => this[propertyName]);
  }
}
