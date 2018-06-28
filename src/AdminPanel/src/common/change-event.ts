import {DOM} from "aurelia-framework";

export class ChangeEvent {
  bubbles = true;

  private constructor(public detail) {
  }

  static newInstance(value?: any): Event {
    return DOM.createCustomEvent('change', new ChangeEvent(value));
  }
}
