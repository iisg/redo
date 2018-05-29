import {autoinject} from "aurelia-dependency-injection";
import {DOM} from "aurelia-framework";
import {bindable} from "aurelia-templating";

@autoinject
export class MoveUpOrDownButtons {
  @bindable showUpButton: boolean = true;
  @bindable showDownButton: boolean = true;

  constructor(private element: Element) {
  }

  dispatchUp() {
    this.dispatchEvent('up');
  }

  dispatchDown() {
    this.dispatchEvent('down');
  }

  private dispatchEvent(eventName: string) {
    let event = DOM.createCustomEvent(eventName, {bubbles: true});
    this.element.dispatchEvent(event);
  }
}
