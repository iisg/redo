import {bindable} from "aurelia-templating";
import {computedFrom, observable} from "aurelia-binding";
import {changeHandler, twoWay} from "common/components/binding-mode";
import {generateId} from "common/utils/string-utils";
import {MinMaxValue} from "resources-config/metadata/metadata-min-max-value";

export class MinMaxValueConstraintEditor {
  @bindable(twoWay) minMaxValue: MinMaxValue;
  @bindable originalMinMaxValue: MinMaxValue;
  @bindable hasBase: boolean;

  private modelIsChangingMinMax: boolean = false;
  @observable(changeHandler('updateConstraintMinMax')) checkmin: boolean;
  @observable(changeHandler('updateConstraintMinMax')) checkmax: boolean;
  @observable(changeHandler('updateConstraintMinMax')) valuemin: number;
  @observable(changeHandler('updateConstraintMinMax')) valuemax: number;

  radioName: string = generateId();

  updateConstraintMinMax(): void {
    if (this.modelIsChangingMinMax) {
      return;
    }
    this.minMaxValue.min = !this.checkmin ? undefined : this.valuemin;
    this.minMaxValue.max = !this.checkmax ? undefined : this.valuemax;
  }

  attached() {
    this.minMaxValueChanged();
  }

  minMaxValueChanged(): void {
    this.modelIsChangingMinMax = true;

    this.checkmin = this.minMaxValue.min != undefined;
    this.checkmax = this.minMaxValue.max != undefined;

    if (this.checkmin || this.valuemin === undefined) {
      this.valuemin = this.minMaxValue.min || 0;
    }

    if (this.checkmax || this.valuemax === undefined) {
      this.valuemax = this.minMaxValue.max || 0;
    }

    this.modelIsChangingMinMax = false;
  }

  resetToOriginalValues() {
    this.minMaxValue.min = this.originalMinMaxValue.min;
    this.minMaxValue.max = this.originalMinMaxValue.max;
    this.minMaxValueChanged();
    this.updateConstraintMinMax();
  }

  @computedFrom('minMaxValue.min', 'minMaxValue.max', 'originalMinMaxValue.min', 'originalMinMaxValue.max')
  get wasModified(): boolean {
    return (this.minMaxValue.min != this.originalMinMaxValue.min) || (this.minMaxValue.max != this.originalMinMaxValue.max);
  }
}
