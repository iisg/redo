import {DOM} from "aurelia-framework";

export class CustomEvent {
  bubbles = true;

  protected constructor(public detail) {
  }

  static newInstance(name: string, value?: any): Event {
    return DOM.createCustomEvent(name, new CustomEvent(value));
  }
}
