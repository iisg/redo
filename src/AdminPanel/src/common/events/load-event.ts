import {CustomEvent} from "./custom-event";

export class LoadEvent extends CustomEvent {
  static newInstance(value?: any): Event {
    return super.newInstance('load', value);
  }
}
