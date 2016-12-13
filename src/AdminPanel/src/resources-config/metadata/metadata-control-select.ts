import {bindable, ComponentAttached} from "aurelia-templating";
import {Configure} from "aurelia-configuration";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";

@autoinject
export class MetadataControlSelect implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: string;

  controls: string[];

  dropdown: Element;

  constructor(config: Configure, private ea: EventAggregator) {
    this.controls = config.get('supported_controls');
  }

  attached() {
    $(this.dropdown).selectpicker();
    this.ea.subscribe('i18n:translation:finished', () => {
      $(this.dropdown).selectpicker('refresh');
    });
  }
}
