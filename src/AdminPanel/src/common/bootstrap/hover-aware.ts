import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class HoverAwareCustomAttribute {
  value: Object;
  private $element: JQuery;

  constructor(element: Element) {
    this.$element = $(element);
  }

  bind() {
    this.$element.on('mouseenter', this.setHovered);
    this.$element.on('mouseleave', this.unsetHovered);
  }

  unbind() {
    this.$element.off('mouseenter', this.setHovered);
    this.$element.off('mouseleave', this.unsetHovered);
  }

  setHovered = () => this.value['hovered'] = true;
  unsetHovered = () => this.value['hovered'] = false;
}
