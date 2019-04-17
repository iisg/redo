import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";

@autoinject
export class CollapsibleCustomAttribute {
  private readonly TRANSITION_END_EVENT_NAMES = 'transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd';

  private element: HTMLElement;
  @bindable({primaryProperty: true}) collapsed: boolean;
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
    if (this.firstChange &&
      ((this.collapsed && this.element.classList.contains('collapsed'))
      || (!this.collapsed && this.element.classList.contains('expanded')))) {
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
    this.$element.on(this.TRANSITION_END_EVENT_NAMES, event => {
      this.$element.off(this.TRANSITION_END_EVENT_NAMES);
      if (event.currentTarget == this.element) {
        this.updateClassesAndProperties();
      }
    });
  }

  private updateClassesAndProperties() {
    if (this.collapsed) {
      this.element.classList.add('collapsed');
      this.element.classList.remove('collapsing');
    } else {
      this.element.classList.add('expanded');
      this.element.classList.remove('expanding');
      this.element.style.width = '';
      this.element.style.height = '';
    }
  }

  private collapse() {
    this.element.style.width = this.targetWidth + 'px';
    this.element.style.height = this.targetHeight + 'px';
    setTimeout(() => {
      this.element.classList.add('collapsing');
      this.element.classList.remove('expanded');
      this.element.classList.remove('expanding');
      this.element.style.width = '0';
      this.element.style.height = '0';
    }, 10);
  }

  private expand() {
    this.element.style.width = this.targetWidth + 'px';
    this.element.style.height = this.targetHeight + 'px';
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
