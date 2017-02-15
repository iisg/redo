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
    this.scheduleRefresh();
  }

  valueChanged() {
    this.scheduleRefresh();
  }

  stopPropagation($event: Event) {
    $event.stopPropagation();
  }

  private findValueIndex(value: Object): number {
    if (!value || !this.values) {
      return undefined;
    }
    let index = this.values.indexOf(this.value);
    if (index == -1 && value.hasOwnProperty('id')) {
      return this.values.map((v) => v['id']).indexOf(value['id']);
    }
    return index;
  }

  private refreshBootstrapSelect() {
    let index = this.findValueIndex(this.value);
    $(this.dropdown).selectpicker('val', index as any);
    $(this.dropdown).selectpicker('refresh');
  }

  private scheduleRefresh() {
    setTimeout(() => {
      this.refreshBootstrapSelect();
    });
  }

  attached() {
    $(this.dropdown).selectpicker({
      liveSearch: this.liveSearch,
      noneResultsText: this.i18n.tr("No matches found for {0}"),
    });
    $(this.dropdown).on('change', this.updateValueFromBootstrap);
    this.refreshBootstrapSelect();
  }

  private updateValueFromBootstrap = (event) => {
    let selectedIndex = event.currentTarget.value;
    this.value = this.values[selectedIndex];
    let changeEvent = DOM.createCustomEvent('change', new BootstrapSelectChangeEvent(this.value));
    this.element.dispatchEvent(changeEvent);
    if (this.clearOnSelect) {
      this.value = undefined;
      this.refreshBootstrapSelect();
    }
  };

  detached() {
    $(this.dropdown).off('change', this.updateValueFromBootstrap);
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
