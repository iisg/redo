import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {DOM} from "aurelia-framework";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class BootstrapSelect implements ComponentAttached, ComponentDetached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Object|Object[];
  @bindable values: Object[];
  @bindable({defaultBindingMode: bindingMode.oneTime}) clearOnSelect: boolean = false;
  @bindable({defaultBindingMode: bindingMode.oneTime}) liveSearch: boolean = false;
  @bindable({defaultBindingMode: bindingMode.oneTime}) multiSelect: boolean = false;
  @bindable disabled: boolean = false;

  dropdown: Element;

  constructor(private element: Element, private i18n: I18N) {
  }

  valuesChanged() {
    this.scheduleRefresh();
  }

  valueChanged() {
    this.scheduleRefresh();
  }

  multiSelectChanged() {
    if (this.clearOnSelect) {
      this.clearOnSelect = false;
    }
  }

  clearOnSelectChanged() {
    if (this.multiSelect) {
      this.multiSelect = false;
    }
  }

  disabledChanged() {
    $(this.dropdown).prop('disabled', this.disabled);
    this.refreshBootstrapSelect();
  }

  stopPropagation($event: Event) {
    $event.stopPropagation();
  }

  public static findValueIndex(value: Object|number, values: Object[]|number[]): number {
    if (value == undefined || values.length == 0) {
      return -1;
    }
    const needle = value.hasOwnProperty('id') ? value['id'] : value;
    const haystack = values[0].hasOwnProperty('id') ? (values as Object[]).map(item => item['id']) : values;
    return haystack.indexOf(needle);
  }

  private refreshBootstrapSelect() {
    let index = Array.isArray(this.value)
      ? (this.value as Object[]).map(value => BootstrapSelect.findValueIndex(value, this.values))
      : BootstrapSelect.findValueIndex(this.value, this.values);
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
    $(this.dropdown).on('change', () => this.updateValueFromBootstrap());
    this.refreshBootstrapSelect();
  }

  private updateValueFromBootstrap() {
    let selectedIndex = $(this.dropdown).val();
    if (selectedIndex == undefined && this.multiSelect) {
      selectedIndex = [];
    }
    this.value = Array.isArray(selectedIndex)
      ? (selectedIndex as number[]).map(i => this.values[i])
      : this.values[selectedIndex];
    let changeEvent = DOM.createCustomEvent('change', new BootstrapSelectChangeEvent(this.value));
    this.element.dispatchEvent(changeEvent);
    if (this.clearOnSelect) {
      this.value = undefined;
      this.refreshBootstrapSelect();
    }
  };

  detached() {
    $(this.dropdown).off('change', () => this.updateValueFromBootstrap());
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
