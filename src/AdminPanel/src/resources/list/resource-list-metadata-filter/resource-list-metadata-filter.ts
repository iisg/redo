import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {bindable} from "aurelia-templating";

@autoinject
export class ResourceListMetadataFilter {
  @bindable metadataId: number;
  @bindable initialValue: string;
  value: string;
  inputBoxVisible: boolean;
  inputBoxFocused: boolean;
  inputBoxSize = 1;

  constructor(private eventAggregator: EventAggregator) {
  }

  bind() {
    if (this.initialValue) {
      this.value = this.initialValue;
      this.inputBoxSize = this.initialValue && this.initialValue.length || 1;
      this.inputBoxVisible = true;
    }
  }

  initialValueChanged() {
    this.value = this.initialValue;
    if (!this.value && !this.inputBoxFocused) {
      this.inputBoxVisible = false;
    }
    this.inputBoxSize = this.value && this.value.length || 1;
    if (this.value) {
      this.inputBoxVisible = true;
    }
  }

  toggleInputBoxVisibility() {
    if (this.inputBoxVisible) {
      if (this.value) {
        this.value = undefined;
        this.publishValueIfItChanged();
      }
      this.takeFocusOutOfInputBoxAndHideIt();
    } else {
      this.showInputBoxAndSetFocusOnIt();
    }
  }

  onInputBoxBlurred() {
    if (!this.value && this.inputBoxVisible) {
      this.takeFocusOutOfInputBoxAndHideIt();
    }
    this.publishValueIfItChanged();
  }

  publishValue() {
    this.initialValue = this.value || undefined;
    this.eventAggregator.publish('metadataFilterValueChanged', {
      metadataId: this.metadataId,
      value: this.value || undefined
    });
  }

  private publishValueIfItChanged() {
    if (this.value || undefined != this.initialValue) {
      this.publishValue();
    }
  }

  private showInputBoxAndSetFocusOnIt() {
    this.inputBoxVisible = true;
    this.inputBoxFocused = true;
  }

  private takeFocusOutOfInputBoxAndHideIt() {
    this.inputBoxFocused = false;
    this.inputBoxVisible = false;
  }
}
