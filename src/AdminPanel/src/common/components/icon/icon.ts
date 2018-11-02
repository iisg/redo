import {bindable} from 'aurelia-templating';

export class Icon {
  @bindable name: string;
  @bindable rotation: number;
  @bindable size = 1;
  path = "/files/icons.svg";
}
