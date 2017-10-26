import {ComponentAttached, customAttribute} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

/**
 * https://github.com/aurelia/templating/issues/233#issuecomment-230766771
 */
@autoinject
@customAttribute('bootstrap-tooltip')
export class BootstrapTooltip implements ComponentAttached {
  private $element: JQuery;
  value: string;

  constructor(element: Element) {
    this.$element = $(element);
  }

  attached() {
    this.attachTooltip();
  }

  private attachTooltip(): void {
    this.$element.tooltip({
        title: this.value,
        container: 'body',
        placement: this.$element.attr('tooltip-placement') || 'top',
        delay: {show: 700, hide: 100}
      }
    );
  }

  unbind() {
    this.$element.tooltip('destroy');
  }

  valueChanged() {
    this.$element.tooltip('hide');
    this.$element.data('bs.tooltip', false);
    this.attachTooltip();
  }
}
