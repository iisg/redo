import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

// Based on https://stackoverflow.com/a/25621277/1937994
@autoinject
export class AutosizeCustomAttribute implements ComponentAttached {
  $textarea: JQuery;

  constructor(element: Element) {
    this.$textarea = $(element);
    this.$textarea.on('input', () => this.resizeTextarea());
  }

  attached() {
    this.resizeTextarea();
  }

  private resizeTextarea() {
    this.$textarea
      .css('height', 'auto')
      .css('height', this.$textarea[0].scrollHeight);
  }
}
