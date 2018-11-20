import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class OnlyValidNumbersCustomAttribute {
  value: boolean;

  constructor(element: Element) {
    element.addEventListener("keypress", (e: KeyboardEvent) => {
      const charCode = String.fromCharCode(e.which);
      let isValidKey = e.metaKey || e.keyCode == 13 || /[0-9]/.test(charCode);
      if (this.value) { // support decimals
        isValidKey = isValidKey || charCode == '.' || charCode == ',';
      }
      if (!isValidKey) {
        e.preventDefault();
      }
    });
  }
}
