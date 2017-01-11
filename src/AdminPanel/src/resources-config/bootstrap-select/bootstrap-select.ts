import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class BootstrapSelect implements ComponentAttached, ComponentDetached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Object;
  @bindable values: Object[];
  @bindable({defaultBindingMode: bindingMode.oneTime}) clearOnSelect = false;
  @bindable({defaultBindingMode: bindingMode.oneTime}) liveSearch = false;

  dropdown: Element;

  constructor(private element: Element, private i18n: I18N) {
  }

  valuesChanged() {
    setTimeout(() => {
      $(this.dropdown).selectpicker('refresh');
    });
  }

  stopPropagation($event: Event) {
    $event.stopPropagation();
  }

  valueChanged() {
    if (this.value) {
      let changeEvent = DOM.createCustomEvent('change', new BootstrapSelectChangeEvent(this.value));
      this.element.dispatchEvent(changeEvent);
      if (this.clearOnSelect) {
        this.value = undefined;
        $(this.dropdown).selectpicker('val', null); // tslint:disable-line:no-null-keyword
        $(this.dropdown).selectpicker('deselectAll');
      }
    }
  }

  attached() {
    $(this.dropdown).selectpicker({
      liveSearch: this.liveSearch,
      noneResultsText: this.i18n.tr("No matches found for {0}"),
    });
  }

  detached() {
    $(this.dropdown).selectpicker('destroy');
  }
}

export class BootstrapSelectChangeEvent<T> {
  bubbles = true;

  detail: {value: T};

  constructor(value: T) {
    this.detail = {value: value};
  }
}
