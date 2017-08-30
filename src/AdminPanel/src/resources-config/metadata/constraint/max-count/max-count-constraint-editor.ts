import {bindable} from "aurelia-templating";
import {computedFrom, observable} from "aurelia-binding";
import {twoWay, changeHandler} from "common/components/binding-mode";
import {generateId} from "common/utils/string-utils";

export class MaxCountConstraintEditor {
  @bindable(twoWay) maxCount: number;
  @bindable baseMaxCount: number;

  private modelIsChanging: boolean = false;
  @observable(changeHandler('updateConstraint')) unlimited: boolean;
  @observable(changeHandler('updateConstraint')) count: number;

  radioName: string = generateId();

  updateConstraint(): void {
    if (this.modelIsChanging) {
      return;
    }
    this.maxCount = this.unlimited ? 0 : this.count;
  }

  maxCountChanged(): void {
    this.modelIsChanging = true;
    this.unlimited = this.maxCount == 0;
    if (!this.unlimited || this.count === undefined) {
      this.count = this.maxCount || 1;
    }
    this.modelIsChanging = false;
  }

  resetToBaseValues() {
    this.maxCount = this.baseMaxCount;
  }

  @computedFrom('maxCount', 'baseMaxCount')
  get wasModified(): boolean {
    return this.maxCount != this.baseMaxCount;
  }

  @computedFrom('baseMaxCount')
  get hasBaseConstraint(): boolean {
    return this.baseMaxCount != undefined;
  }
}
