import {ComponentAttached, ComponentDetached, bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode} from "aurelia-binding";
import {I18N} from "aurelia-i18n";
import * as $ from "jquery";
import "select2";

@autoinject
export class DropdownSelect implements ComponentAttached, ComponentDetached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: Object|Object[];
  @bindable values: Object[];
  @bindable allowClear: boolean = false;
  @bindable placeholder: string = "-";
  @bindable multiple: boolean = false;
  @bindable disabled: boolean = false;

  dropdown: Element;

  constructor(private i18n: I18N) {
  }

  attached(): void {
    $(this.dropdown).select2({
      placeholder: this.placeholder,
      allowClear: this.allowClear,
      multiple: this.multiple,
      width: '100%',
      "language": {
        "noResults": () => this.setNoResultText()
      },
      escapeMarkup: this.escapeMarkup,
      templateResult: data => this.formatOption(data),
      templateSelection: data => this.formatOption(data)
    });

    $(this.dropdown).on('select2:select', () => {
      this.onSelectedValue();
    });
  }

  escapeMarkup(markup) {
    return markup;
  }

  setNoResultText() {
    return this.i18n.tr("No results");
  }

  formatOption(data) {
    if (data.id) {
      return $(this.dropdown).find(`option[value=${data.id}]`).html();
    }
    return '';
  }

  onSelectedValue() {
    let selectedIndex = $(this.dropdown).val();
    if (selectedIndex == undefined && this.multiple) {
      selectedIndex = [];
    }
    this.value = Array.isArray(selectedIndex)
      ? (selectedIndex as number[]).map(index => this.values[index])
      : this.values[selectedIndex];
  }

  refreshSelect() {
    $(this.dropdown).select2('data');
  }

  valuesChanged() {
    this.refreshSelect();
  }

  valueChanged() {
    this.updateOption();
  }

  disabledChanged() {
    if (this.disabled as any === '') { // when used without value: <dropdown-select disabled>
      this.disabled = true;
    }
  }

  updateOption() {
    let values = [];
    if (this.multiple) {
      for (let i in this.values) {
        for (let j in this.value) {
          if (this.values[i] === this.value[j]) {
            values.push(i.toString());
            break;
          }
        }
      }
    } else {
      for (let i in this.values) {
        if (this.values[i] === this.value) {
          values.push(i.toString());
          break;
        }
      }
    }
    $(this.dropdown).val(values);
  }

  detached(): void {
    $(this.dropdown).select2('destroy');
  }
}
