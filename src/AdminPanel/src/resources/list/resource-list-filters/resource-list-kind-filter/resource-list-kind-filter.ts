import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {ResourceListFilter} from "../resource-list-filter";
import {bindable} from "aurelia-templating";
import {FilterChangedEvent} from "resources/list/resources-list-filters";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {BindingEngine, Disposable} from "aurelia-binding";
import {diff} from "common/utils/array-utils";

@autoinject
export class ResourceListKindFilter extends ResourceListFilter {
  @bindable resourceKindList: ResourceKind[];
  @bindable resourceKinds: ResourceKind[] = [];
  @bindable initialValue: number[];
  @bindable eventTarget: string;

  private resourceKindsChangedListener: Disposable;

  constructor(private eventAggregator: EventAggregator, private bindingEngine: BindingEngine) {
    super();
  }

  bind() {
    if (this.initialValue && this.initialValue.length) {
      this.resourceKinds = this.initialValue.map(id => this.resourceKindList.find(rk => rk.id === id));
      this.inputBoxVisible = true;
    }
    this.resourceKindsChangedListener = this.bindingEngine.collectionObserver(this.resourceKindList).subscribe(() => {
      this.resourceKinds.filter(resourceKind => this.resourceKindList.map(rk => rk.id).indexOf(resourceKind.id) != -1);
      this.publishValue();
    });
  }

  detached() {
    this.resourceKindsChangedListener.dispose();
  }

  toggleInputBoxVisibility() {
    if (this.inputBoxVisible) {
      if (this.resourceKinds.length) {
        this.resourceKinds = [];
        this.publishValueIfItChanged();
      }
      this.removeFocusFromInputBoxAndHideIt();
    } else {
      this.showInputBoxAndSetFocusOnIt();
    }
  }

  private publishValueIfItChanged() {
    if (this.resourceKinds || undefined != this.initialValue) {
      this.publishValue();
    }
  }

  resourceKindsChanged(newValue: ResourceKind[], previousValue: ResourceKind[]) {
    if (newValue && previousValue && (diff(newValue, previousValue).length || diff(previousValue, newValue).length)) {
      this.publishValue();
    }
  }

  publishValue() {
    const filterValues = this.resourceKinds.map(resourceKind => resourceKind.id);
    this.initialValue = filterValues;
    this.eventAggregator.publish('kindFilterValueChanged', {
      value: filterValues,
      target: this.eventTarget
    } as FilterChangedEvent<KindsFilterChange>);
  }
}

export type KindsFilterChange = number[];