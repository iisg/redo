import {containerless, bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

@containerless
export class FaStack {
  @bindable largeIcon: string = 'square';
  @bindable smallIcon: string;

  @computedFrom('largeIcon')
  get largeIconClass(): string {
    return `fa-${this.largeIcon}`;
  }

  @computedFrom('smallIcon')
  get smallIconClass(): string {
    return `fa-${this.smallIcon}`;
  }
}
