import {customAttribute} from "aurelia-templating";
import {booleanAttribute} from "../boolean-attribute";

@customAttribute('disabled-link')
export class DisabledLink {
  @booleanAttribute value: boolean;

  constructor(private link: Element) {
  }

  valueChanged(newValue) {
    if (newValue) {
      this.link.classList.add('disabled');
      this.link.addEventListener('click', this.preventLinkClick);
    } else {
      this.link.classList.remove('disabled');
      this.link.removeEventListener('click', this.preventLinkClick);
    }
  }

  private preventLinkClick(event: MouseEvent) {
    event.preventDefault();
    return false;
  }
}
