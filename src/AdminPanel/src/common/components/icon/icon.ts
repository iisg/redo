import {bindable} from 'aurelia-templating';

export class Icon {
    @bindable name: string;
    @bindable rotation: number;
    path = "/files/icons.svg";
}