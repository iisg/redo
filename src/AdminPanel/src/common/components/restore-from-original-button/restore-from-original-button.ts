import {EventAggregator} from "aurelia-event-aggregator";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class RestoreFromOriginalButton {

  constructor(private eventAggregator: EventAggregator) {
  }

  metadataChangeEvent() {
    this.eventAggregator.publish('metadataChanged');
  }
}
