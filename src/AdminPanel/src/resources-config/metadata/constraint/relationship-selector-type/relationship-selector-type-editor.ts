import {bindable, ComponentBind} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "common/components/binding-mode";
import {values} from "lodash";

export class RelationshipSelectorTypeEditor implements ComponentBind {
  @bindable(twoWay) value: string;
  @bindable originalValue: string;
  @bindable hasBase: boolean;
  types: string[] = values(RelationshipSelectorType);

  bind() {
    if (!this.value) {
      this.value = RelationshipSelectorType.SIMPLE;
    }
  }

  @computedFrom('value', 'originalValue')
  get wasModified(): boolean {
    return this.value != this.originalValue;
  }

  resetToOriginalValues() {
    this.value = this.originalValue;
  }
}

export enum RelationshipSelectorType {
  SIMPLE = 'simple',
  TREE = 'tree',
}
