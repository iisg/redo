import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";

// Based on https://stackoverflow.com/a/25621277/1937994.
@autoinject
export class AutosizeCustomAttribute implements ComponentAttached {
  private readonly MINIMUM_HEIGHT = 50;

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
      .css('height', Math.max(this.$textarea[0].scrollHeight + Math.ceil(parseFloat(this.$textarea.css('border-top-width')))
        + Math.ceil(parseFloat(this.$textarea.css('border-bottom-width'))), this.MINIMUM_HEIGHT));
  }
}
