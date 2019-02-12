import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ResourceListFilters} from "../resource-list-filters";
import {bindable} from "aurelia-templating";

@autoinject
export class ResourceListMetadataFilter extends ResourceListFilters {
  @bindable metadataId: number;
  @bindable initialValue: string;
  value: string;
  inputBoxSize = 1;

  constructor(private eventAggregator: EventAggregator) {
    super();
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
      this.removeFocusFromInputBoxAndHideIt();
    } else {
      this.showInputBoxAndSetFocusOnIt();
    }
  }

  onInputBoxBlurred() {
    if (!this.value && this.inputBoxVisible) {
      this.removeFocusFromInputBoxAndHideIt();
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
}
