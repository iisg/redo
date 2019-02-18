import {CustomEvent} from "./custom-event";

export class ChangeEvent extends CustomEvent {
  static newInstance(value?: any): Event {
    return super.newInstance('change', value);
  }
}
