import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class OnlyValidNumbersCustomAttribute {
  constructor(element: Element) {
    element.addEventListener("keypress", (e: KeyboardEvent) => {
      let isValidKey = e.metaKey || e.keyCode == 13 || /[0-9]/.test(String.fromCharCode(e.which));
      if (!isValidKey) {
        e.preventDefault();
      }
    });
  }
}
