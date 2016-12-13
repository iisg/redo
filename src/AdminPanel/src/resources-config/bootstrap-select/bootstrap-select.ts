import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class BootstrapSelect implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Object;

  @bindable values: Object[];

  @bindable({defaultBindingMode: bindingMode.oneTime}) clearOnSelect = false;
  @bindable({defaultBindingMode: bindingMode.oneTime}) title: string;
  @bindable({defaultBindingMode: bindingMode.oneTime}) liveSearch = false;

  dropdown: Element;

  constructor(private element: Element, private ea: EventAggregator) {
  }

  valuesChanged() {
    setTimeout(() => {
      $(this.dropdown).selectpicker('refresh');
    });
  }

  stopPropagation($event: Event) {
    $event.stopPropagation();
  }

  attached() {
    $(this.dropdown).selectpicker({
      liveSearch: this.liveSearch,
      title: this.title,
      noneResultsText: "Żaden element nie pasuje do {0}",
    });
    $(this.dropdown).on('changed.bs.select', () => {
      let changeEvent = DOM.createCustomEvent('change', new BootstrapSelectChangeEvent(this.value));
      this.element.dispatchEvent(changeEvent);
      if (this.clearOnSelect) {
        delete this.value;
        $(this.dropdown).selectpicker('val', null); // tslint:disable-line:no-null-keyword
        $(this.dropdown).selectpicker('deselectAll');
      }
    });
    this.ea.subscribe('i18n:translation:finished', () => {
      setTimeout(() => {
        $(this.dropdown).selectpicker('refresh');
      });
    });
  }
}

export class BootstrapSelectChangeEvent<T> {
  bubbles = true;

  detail: {value: T};

  constructor(value: T) {
    this.detail = {value: value};
  }
}
