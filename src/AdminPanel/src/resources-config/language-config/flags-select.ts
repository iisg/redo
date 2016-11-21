import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";

@autoinject
export class FlagsSelect implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: string;

  availableFlags: string[];

  dropdown: Element;

  constructor(private httpClient: HttpClient) {
  }

  attached() {
    this.httpClient.get('/flags.json').then((response) => {
      this.availableFlags = response.content.available_flags;
      setTimeout(() => $(this.dropdown).selectpicker());
    });
  }
}
