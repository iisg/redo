import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import * as $ from "jquery";
import "select2";
import {booleanAttribute} from "../boolean-attribute";
import {changeHandler, twoWay} from "../binding-mode";
import {computedFrom} from "aurelia-binding";
import {DOM} from "aurelia-framework";

@autoinject
export class DropdownSelect implements ComponentAttached, ComponentDetached {
  @bindable(twoWay) value: Object | Object[];
  @bindable values: Object[];

  @bindable(changeHandler('recreateDropdown')) placeholder: string = "—";
  @bindable @booleanAttribute multiple: boolean = false;
  @bindable @booleanAttribute allowClear: boolean = false;  // allows nothing in single-select dropdown

  @bindable @booleanAttribute hideSearchBox: boolean = false;

  @bindable @booleanAttribute disabled: boolean = false;

  dropdown: Element;

  constructor(private i18n: I18N, private element: Element) {
  }

  attached(): void {
    this.createDropdown();
  }

  detached(): void {
    $(this.dropdown).select2('destroy');
  }

  multipleChanged() {  // @booleanAttributes enforce default handler name
    this.recreateDropdown();
  }

  allowClearChanged() {  // @booleanAttributes enforce default handler name
    this.recreateDropdown();
  }

  private createDropdown() {
    this.recreateDropdown();
    $(this.dropdown).on('select2:select', () => this.onSelectedItem());
    // timeout necessary because event fires before changing value: https://github.com/select2/select2/issues/5049
    $(this.dropdown).on('select2:unselect', () => setTimeout(() => this.onSelectedItem()));
  }

  private recreateDropdown(): JQuery {
    const $element = $(this.dropdown).select2({
      placeholder: this.placeholder,
      allowClear: this.allowClear,
      multiple: this.multiple,
      minimumResultsForSearch: this.hideSearchBox ? -1 : 0,
      width: '100%',
      language: {
        "noResults": () => this.i18n.tr("No results")
      },
      escapeMarkup: v => v,
      templateResult: item => this.getItemHtml(item),
      templateSelection: item => this.getItemHtml(item)
    });
    this.updateSelectedItem();
    return $element;
  }

  valuesChanged() {
    setTimeout(() => {
      this.createDropdown();
    });
    this.valueChanged();
  }

  valueChanged() {
    setTimeout(() => {
      this.updateSelectedItem();
    });
  }

  onSelectedItem() {  // copy value from DOM to VM
    let selectedIndex = $(this.dropdown).val();
    if (selectedIndex == undefined && this.multiple) {
      selectedIndex = [];
    }
    this.value = Array.isArray(selectedIndex)
      ? (selectedIndex as number[]).map(index => this.values[index])
      : this.values[selectedIndex];
    setTimeout(() => this.element.dispatchEvent(ChangeEvent.newInstance()));
  }

  updateSelectedItem() {  // copy value from VM to DOM
    if (this.value == undefined || this.values == undefined || this.values.length == 0) {
      $(this.dropdown).val([] as any).trigger('change');
      return;
    }
    const needles: number[] = Array.isArray(this.value)
      ? (this.value as Object[]).map(value => this.getIndex(value))
      : [this.getIndex(this.value)];
    const haystack: number[] = this.values.map(this.getIndex);
    const values: number[] = needles.map(id => haystack.indexOf(id)).filter(index => index != -1);
    const value: number | number[] = Array.isArray(this.value)
      ? values
      : values[0];
    $(this.dropdown).val(value as any).trigger('change');
  }

  getIndex(value: any) {
    return value !== undefined && value.hasOwnProperty('id')
      ? value['id']
      : value;
  }

  getItemHtml(item: Select2SelectionObject): string {
    return item.element ? $(item.element).html() : item.text;
  }

  @computedFrom('values')
  get isFetchingOptions() {
    return !this.values;
  }
}

class ChangeEvent {
  bubbles = true;

  static newInstance(): Event {
    return DOM.createCustomEvent('change', new ChangeEvent());
  }
}
