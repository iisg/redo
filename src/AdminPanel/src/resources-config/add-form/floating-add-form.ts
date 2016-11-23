import {bindable} from "aurelia-templating";

export class FloatingAddForm {
  @bindable
  controller: FloatingAddFormController;

  formOpened: boolean = false;

  attached() {
    if (this.controller) {
      this.controller.hide = () => this.formOpened = false;
    }
  }
}

export interface FloatingAddFormController {
  hide?: () => void;
}
