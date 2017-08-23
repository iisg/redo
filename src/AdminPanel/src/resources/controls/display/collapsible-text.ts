import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {firstLineWithContent, trimToLengthBetweenWords} from "../../../common/utils/string-utils";
import {booleanAttribute} from "../../../common/components/boolean-attribute";

@autoinject
export class CollapsibleText {
  @bindable text: string = '';
  @bindable maxLength: number = 60;
  @bindable @booleanAttribute collapsed: boolean = false;
  @bindable @booleanAttribute multiLine: boolean = false;

  @computedFrom('text', 'maxLength')
  get collapsedText(): string {
    const firstLine = firstLineWithContent(this.text);
    if (firstLine.length <= this.maxLength) {
      return firstLine;
    }
    const excerptLength = this.maxLength * 0.9; // guarantees that at least 10% of text is trimmed to avoid irritating short trims
    return trimToLengthBetweenWords(firstLine, excerptLength);
  }

  @computedFrom('text', 'collapsedText')
  get amountHidden(): string {
    const exactAmountHidden = 1 - (this.collapsedText.length / this.text.length);
    const multipleOfFivePercent = Math.round(exactAmountHidden * 20) * 5;
    return (multipleOfFivePercent == 100)
      ? '99%'
      : multipleOfFivePercent + '%';
  }

  @computedFrom('text', 'collapsedText')
  get textIsCollapsible(): boolean {
    return this.collapsedText != this.text;
  }

  @computedFrom('text')
  get textLines(): string[] {
    return this.text.split(/(\n|\r|\r\n)/g);
  }
}
