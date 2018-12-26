import {computedFrom, observable} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {changeHandler, twoWay} from "common/components/binding-mode";
import {generateId} from "common/utils/string-utils";

export class MaxCountConstraintEditor {
  @bindable(twoWay) maxCount: number;
  @bindable originalMaxCount: number;
  @bindable hasBase: boolean;

  private modelIsChanging: boolean = false;
  @observable(changeHandler('updateConstraint')) isUnlimited: boolean;
  @observable(changeHandler('updateConstraint')) count: number;

  radioName: string = generateId();
  private readonly DISPLAYED_COUNT_IF_UNLIMITED = 1;
  private readonly UNLIMITED_COUNT = -1;

  updateConstraint(): void {
    if (this.modelIsChanging) {
      return;
    }
    if (this.isUnlimited) {
      this.count = this.DISPLAYED_COUNT_IF_UNLIMITED;
    }
    this.maxCount = this.isUnlimited ? this.UNLIMITED_COUNT : this.count;
  }

  attached() {
    this.maxCountChanged();
  }

  maxCountChanged(): void {
    this.modelIsChanging = true;
    this.isUnlimited = !this.maxCount || this.maxCount == this.UNLIMITED_COUNT;
    this.count = this.isUnlimited ? this.DISPLAYED_COUNT_IF_UNLIMITED : this.maxCount;
    this.modelIsChanging = false;
  }

  resetToOriginalValues() {
    this.maxCount = this.originalMaxCount;
  }

  @computedFrom('maxCount', 'originalMaxCount')
  get wasModified(): boolean {
    return this.maxCount != this.originalMaxCount && !(this.maxCount == this.UNLIMITED_COUNT && this.originalMaxCount == undefined);
  }
}
