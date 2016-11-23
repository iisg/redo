import {observable, bindingMode} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {inject} from "aurelia-dependency-injection";
import {DOM} from "aurelia-framework";
import {generateId} from "../../common/utils/string-utils";
import {Language} from "../language-config/language";

@inject(Element)
export class MultilingualText {
  @observable
  currentLanguage: Language;

  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: Object = {};

  @bindable
  placeholder: Object = {};

  @bindable
  label: string;

  fieldId: string = generateId();

  textInput: HTMLElement;

  constructor(private element: HTMLElement) {
    this.element.focus = () => this.textInput.focus();
  }

  valueChanged(newValue: Object) {
    if (!newValue) {
      this.value = {};
    }
  }

  currentLanguageChanged(newValue: string, oldValue: string) {
    if (oldValue) {
      this.textInput.focus();
    }
  }

  /**
   * Simulate blur event to the parent component when the text input is blurred.
   * @see https://www.danyow.net/aurelia-validation-alpha/#myforminputsarecustomelementsisthatsupported
   */
  blur() {
    let blurEvent = DOM.createCustomEvent('blur', {});
    this.element.dispatchEvent(blurEvent);
  }
}
