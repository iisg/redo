import {parse as parseYaml, stringify as stringifyYaml} from "yamljs";
import {twoWay} from "common/components/binding-mode";
import {bindable} from "aurelia-templating";
import {observable} from "aurelia-binding";
import {mapValues} from "lodash";

export class ResourceContentsYamlFilter {
  @bindable(twoWay) value: Object;
  @bindable(twoWay) rerender: () => void;

  @observable yamlValue: string;
  yamlValid = true;

  attached() {
    this.rerender = this.rerenderYamlValue;
  }

  valueChanged() {
    if (this.value && !this.yamlValue) {
      this.rerenderYamlValue();
    }
  }

  private rerenderYamlValue = () => {
    let value = this.value || {};
    if (Object.keys(value).length > 0) {
      this.yamlValue = stringifyYaml(this.nullStringsToNulls(value), 1, 2);
    } else {
      this.yamlValue = '';
    }
  };

  yamlValueChanged() {
    this.yamlValid = true;
    if (this.yamlValue) {
      try {
        const parsed = parseYaml(this.yamlValue);
        if (typeof parsed == 'object') {
          if (Object.keys(parsed).length > 0) {
            this.value = this.nullsToStrings(parsed);
          } else {
            this.value = undefined;
          }
          return;
        }
      } catch (e) {
      }
    } else {
      this.value = {};
      return;
    }
    this.value = undefined;
    this.yamlValid = !this.yamlValue;
  }

  private nullsToStrings(object) {
    return mapValues(object, value => value === null ? 'null' : value) // tslint:disable-line
  }

  private nullStringsToNulls(object) {
    return mapValues(object, value => value === 'null' ? null : value); // tslint:disable-line
  }
}
