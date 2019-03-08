import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class CollapsibleCustomAttribute {
  private element: HTMLElement;
  @bindable collapsed: boolean;
  @bindable width: number;
  @bindable height: number;
  private $element: JQuery;
  private firstChange = true;

  constructor(element: Element) {
    this.element = element as HTMLElement;
    this.$element = $(element);
  }

  bind() {
    if (this.element.classList.contains('collapsed')) {
      this.collapsed = true;
    } else if (this.element.classList.contains('expanded')) {
      this.collapsed = false;
    } else {
      this.element.classList.add(this.collapsed ? 'collapsed' : 'expanded');
    }
    if (this.collapsed) {
      this.element.style.width = '0';
      this.element.style.height = '0';
    }
    this.element.classList.remove('collapsing');
    this.element.classList.remove('expanding');
  }

  collapsedChanged() {
    if (this.firstChange && !this.collapsed && this.element.classList.contains('expanded')) {
      this.firstChange = false;
    } else {
      this.watchForTransitionsEnding();
      if (this.collapsed) {
        this.collapse();
      } else {
        this.expand();
      }
    }
  }

  private watchForTransitionsEnding() {
    let eventReceived = false;
    $(this.element).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', event => {
      if (event.target == this.element && !eventReceived) {
        eventReceived = true;
        this.updateClassesAndProperties();
      }
    });
  }

  private updateClassesAndProperties() {
    if (this.collapsed) {
      this.element.classList.add('collapsed');
      this.element.classList.remove('collapsing');
    }
    else {
      this.element.classList.add('expanded');
      this.element.classList.remove('expanding');
      this.element.style.width = '';
      this.element.style.height = '';
    }
  }

  private collapse() {
    this.width = this.targetWidth;
    this.element.style.width = this.width + 'px';
    this.height = this.targetHeight;
    this.element.style.height = this.height + 'px';
    setTimeout(() => {
      this.element.style.width = '0';
      this.element.style.height = '0';
      this.element.classList.add('collapsing');
      this.element.classList.remove('expanded');
      this.element.classList.remove('expanding');
    }, 10);
  }

  private expand() {
    if (!this.width) {
      this.width = this.targetWidth;
    }
    if (!this.height) {
      this.height = this.targetHeight;
    }
    this.element.style.width = this.width + 'px';
    this.element.style.height = this.height + 'px';
    this.element.classList.add('expanding');
    this.element.classList.remove('collapsed');
    this.element.classList.remove('collapsing');
  }

  private get targetWidth(): number {
    return this.element.scrollWidth
      + this.propertyValueAsNumber('border-left-width') + this.propertyValueAsNumber('border-right-width');
  }

  private get targetHeight(): number {
    return this.element.scrollHeight
      + this.propertyValueAsNumber('border-top-width') + this.propertyValueAsNumber('border-bottom-width');
  }

  private propertyValueAsNumber(propertyName: string) {
    return Math.ceil(parseFloat(this.$element.css(propertyName)));
  }
}
