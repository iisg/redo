import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class MoveUpDownButtons {
  @bindable showUp: boolean = true;
  @bindable showDown: boolean = true;

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
