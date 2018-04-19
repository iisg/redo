import {parse as parseYaml, stringify as stringifyYaml} from "yamljs";
import {twoWay} from "../../common/components/binding-mode";
import {bindable} from "aurelia-templating";
import {observable} from "aurelia-binding";

export class ResourceContentsYamlFilter {
  @bindable(twoWay) value: Object;

  @observable yamlValue: string;
  yamlValid = true;

  valueChanged() {
    if (this.value && !this.yamlValue) {
      this.yamlValue = stringifyYaml(this.value || {});
    }
  }

  yamlValueChanged() {
    this.yamlValid = true;
    if (this.yamlValue) {
      try {
        const parsed = parseYaml(this.yamlValue);
        if (typeof parsed == 'object') {
          if (Object.keys(parsed).length > 0) {
            this.value = parsed;
          } else {
            this.value = undefined;
          }
          return;
        }
      } catch (e) {
      }
    }
    this.value = undefined;
    this.yamlValid = !this.yamlValue;
  }
}
