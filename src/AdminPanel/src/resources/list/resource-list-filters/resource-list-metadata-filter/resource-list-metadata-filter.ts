import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ResourceListFilter} from "../resource-list-filter";
import {bindable} from "aurelia-templating";
import {FilterChangedEvent} from "resources/list/resources-list-filters";

@autoinject
export class ResourceListMetadataFilter extends ResourceListFilter {
  @bindable metadataId: number;
  @bindable initialValue: string;
  @bindable disabled: boolean;
  @bindable eventTarget: string;
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
    if (!this.disabled) {
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
      value: {
        metadataId: this.metadataId,
        value: this.value || undefined
      },
      target: this.eventTarget
    } as FilterChangedEvent<MetadataFilterChange>);
  }

  private publishValueIfItChanged() {
    if (this.value || undefined != this.initialValue) {
      this.publishValue();
    }
  }
}

export interface MetadataFilterChange {
  metadataId: number;
  value: string;
}
