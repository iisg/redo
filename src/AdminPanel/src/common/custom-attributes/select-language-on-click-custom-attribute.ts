import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {GuiLanguage} from "common/i18n/gui-language";

@autoinject
export class SelectLanguageOnClickCustomAttribute {
  @bindable({ primaryProperty: true }) language: string;

  constructor(element: Element, guiLanguage: GuiLanguage) {
    (element as HTMLElement).onclick = () => guiLanguage.changeLanguage(this.language);
  }
}
