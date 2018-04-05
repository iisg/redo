import {bindable} from "aurelia-templating";
import {computedFrom, observable} from "aurelia-binding";
import {changeHandler, twoWay} from "common/components/binding-mode";
import {generateId} from "common/utils/string-utils";

export class MaxCountConstraintEditor {
  @bindable(twoWay) maxCount: number;
  @bindable originalMaxCount: number;
  @bindable hasBase: boolean;

  private modelIsChanging: boolean = false;
  @observable(changeHandler('updateConstraint')) unlimited: boolean;
  @observable(changeHandler('updateConstraint')) count: number;

  radioName: string = generateId();

  updateConstraint(): void {
    if (this.modelIsChanging) {
      return;
    }
    if (this.unlimited) {
      this.count = 1;
    }
    this.maxCount = this.unlimited ? undefined : this.count;
  }

  attached() {
    this.maxCountChanged();
  }

  maxCountChanged(): void {
    this.modelIsChanging = true;
    this.count = this.maxCount || 1;
    this.unlimited = (this.maxCount == undefined);
    this.modelIsChanging = false;
  }

  resetToOriginalValues() {
    this.maxCount = this.originalMaxCount;
  }

  @computedFrom('maxCount', 'originalMaxCount')
  get wasModified(): boolean {
    return this.maxCount != this.originalMaxCount;
  }
}
