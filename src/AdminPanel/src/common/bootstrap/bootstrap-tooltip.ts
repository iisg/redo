import {customAttribute} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

/**
 * https://github.com/aurelia/templating/issues/233#issuecomment-230766771
 */
@autoinject
@customAttribute('bootstrap-tooltip')
export class BootstrapTooltip {
  private $element: JQuery;

  value: string;

  constructor(element: Element) {
    this.$element = $(element);
  }

  bind() {
    this.$element.tooltip({
        title: this.value,
        container: 'body',
        delay: {show: 700, hide: 100}
      }
    );
  }

  unbind() {
    this.$element.tooltip('destroy');
  }

  valueChanged() {
    this.$element.data('bs.tooltip', false);
    this.bind();
  }
}