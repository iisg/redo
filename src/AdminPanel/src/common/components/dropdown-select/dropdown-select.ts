import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import * as $ from "jquery";
import {debounce} from "lodash";
import "select2";
import {ChangeEvent} from "../../change-event";
import {changeHandler, twoWay} from "../binding-mode";
import {booleanAttribute} from "../boolean-attribute";

@autoinject
export class DropdownSelect implements ComponentAttached, ComponentDetached {
  @bindable values: Object[];
  @bindable(twoWay) value: Object | Object[];
  @bindable(changeHandler('setupDropdownAgain')) placeholder = "—";
  @bindable @booleanAttribute setFirstAsDefault: boolean;
  @bindable @booleanAttribute multiple: boolean;
  @bindable @booleanAttribute hideSearchBox: boolean;
  @bindable @booleanAttribute hideClearButton: boolean;
  @bindable @booleanAttribute useComputedWidth: boolean;
  @bindable @booleanAttribute disabled: boolean;
  @bindable @booleanAttribute clearAfterSelect: boolean;
  @bindable searchFunction: ({term, page}) => Promise<{results, pagination: {more: boolean, itemsPerPage: number}}>;
  @bindable formatter: ({item}) => {text: string};
  dropdown: Element;

  constructor(private i18n: I18N, private element: Element) {
  }

  attached() {
    this.setupDropdown();
  }

  detached() {
    $(this.dropdown).select2('destroy');
  }

  valuesChanged() {
    if (!this.searchFunction) {
      this.setupDropdownAgain();
    }
    if (!this.multiple && this.setFirstAsDefault) {
      if (this.value) {
        this.value = this.values.find(value => value == this.value);
      } else {
        this.value = this.values[0];
      }
    }
  }

  valueChanged() {
    setTimeout(() => this.updateSelectedItem());
  }

  multipleChanged() {  // @booleanAttributes enforce default handler name.
    this.setupDropdownAgain();
  }

  hideClearButtonChanged() {  // @booleanAttributes enforce default handler name.
    this.setupDropdownAgain();
  }

  private setupDropdownAgain() {
    if (this.dropdown) {
      setTimeout(() => this.setupDropdown());
    }
  }

  private setupDropdown() {
    this.createDropdown().on('select2:select', () => this.onSelectedItem())
      // Timeout necessary because event fires before changing value: https://github.com/select2/select2/issues/5049 .
      .on('select2:unselect', () => setTimeout(() => this.onSelectedItem()))
      // Prevents dropdown to appear after clearing a value: https://github.com/select2/select2/issues/3320#issuecomment-350249668 .
      .on('select2:unselecting', (event) => {
        let originalEvent = (event as any).params.args.originalEvent;
        if (originalEvent) {
          originalEvent.stopPropagation();
        } else {
          $(this.dropdown).one('select2:opening', (event) => event.preventDefault());
        }
      });
  }

  private createDropdown(): JQuery {
    const options = this.select2Options();
    if (this.searchFunction) {
      $.extend(options, this.select2DynamicLoadingOptions());
    }
    const $element = $(this.dropdown).select2(options);
    if (this.useComputedWidth && !this.multiple) {
      const container = $element.siblings('.select2-container');
      const currentWidth = parseInt(container.css('width'));
      if (currentWidth) {
        container.css({'width': (currentWidth + 10) + 'px'});
      }
    }
    this.updateSelectedItem();
    return $element;
  }

  private select2Options(): Select2Options {
    return {
      placeholder: this.placeholder,
      multiple: this.multiple,
      minimumResultsForSearch: this.hideSearchBox ? -1 : 0,
      allowClear: !this.hideClearButton,
      width: this.useComputedWidth ? undefined : '100%',
      language: {
        noResults: () => this.i18n.tr("No results"),
        loadingMore: () => this.i18n.tr("Loading more results") + "...",
        searching: () => this.i18n.tr("Searching") + "..."
      },
      sorter: (data) => {
        if (this.value == undefined) {
          return data;
        }
        const valueIsAnArray = Array.isArray(this.value);
        if (valueIsAnArray && !(this.value as Object[]).length) {
          return data;
        }
        let results: any[];
        if (valueIsAnArray) {
          if (typeof(this.value[0]) == 'number' && data.length && data[0].element.model.hasOwnProperty('id')) {
            results = data.filter(item => !(this.value as Object[]).includes(item.element.model.id));
          } else {
            results = data.filter(item => !(this.value as Object[]).includes(item.element.model));
          }
        } else {
          results = data.filter(item => item.element.model != this.value);
        }
        if (data.length && !results.length) {
          $(this.dropdown).select2('close');
        }
        return results;
      },
      escapeMarkup: v => v,
      templateResult: item => this.getItemHtml(item),
      templateSelection: item => this.getItemHtml(item)
    };
  }

  private select2DynamicLoadingOptions(): {ajax: Select2AjaxOptions} {
    return {
      ajax: {
        data: (params: any) => ({
            term: params.term || '',
            page: params.page || 1
        }),
        transport: (params: JQueryAjaxSettings,
                    success: (data: any) => undefined, failure: () => undefined): JQueryXHR => {
          const term = params.data.term || '';
          this.searchFunction({term: term, page: params.data.page})
            .then(data => {
              const indexInPageIdDifference = (params.data.page - 1) * data.pagination.itemsPerPage;
              data['results'] = data.results.map((item, index) => {
                return $.extend(this.formatter({item}), {id: index + indexInPageIdDifference});
              });
              return data;
            })
            .then(success)
            .catch(failure);
          return undefined;
        }
      }
    };
  }

  onSelectedItem() {  // Copy value from DOM to VM.
    let selectedIndex = $(this.dropdown).val();
    if (selectedIndex == undefined && this.multiple) {
      selectedIndex = [];
    }
    if (Array.isArray(selectedIndex)) {
      if (selectedIndex.length == 1 && selectedIndex[0] === '') {
        this.value = [];
      } else {
        this.value = (selectedIndex as number[]).map(index => this.values[index]);
      }
    } else {
      this.value = this.values[selectedIndex];
    }
    this.dispatchChangedEvent(this.value);
  }

  dispatchChangedEvent = debounce((value) => this.element.dispatchEvent(ChangeEvent.newInstance(value)), 10);

  updateSelectedItem() {  // Copy value from VM to DOM.
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
    if (this.clearAfterSelect) {
      this.value = undefined;
    }
  }

  getIndex(value: any) {
    return value !== undefined && value.hasOwnProperty('id')
      ? value['id']
      : value;
  }

  getItemHtml(item: Select2SelectionObject): string {
    return item.element ? $(item.element).html() : item.text;
  }

  @computedFrom('values', 'searchFunction')
  get isFetchingOptions() {
    return !this.values && !this.searchFunction;
  }
}
