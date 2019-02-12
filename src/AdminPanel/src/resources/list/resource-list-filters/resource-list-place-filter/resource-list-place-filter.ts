import {ResourceListFilters} from "../resource-list-filters";
import {WorkflowPlace} from "../../../../workflows/workflow";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {bindable, ComponentBind} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {inArray, diff} from "common/utils/array-utils";
import {observable, BindingEngine, Disposable} from "aurelia-binding";
import {ComponentDetached} from "aurelia-templating";

@autoinject
export class ResourceListPlaceFilter extends ResourceListFilters implements ComponentBind, ComponentDetached {
  @bindable resourceKinds: ResourceKind[];
  @bindable initialValue: string[];
  @observable places: WorkflowPlace[] = [];

  placesList: WorkflowPlace[] = [];

  private resourceKindsChangedListener: Disposable;

  constructor(private eventAggregator: EventAggregator, private bindingEngine: BindingEngine) {
    super();
  }

  bind() {
    if (this.initialValue && this.initialValue.length) {
      this.places = this.getPlacesByPlaceIds(this.initialValue);
      this.inputBoxVisible = true;
    }
    this.placesList = this.workflowPlaces();
    this.resourceKindsChangedListener = this.bindingEngine.collectionObserver(this.resourceKinds).subscribe(() => {
      this.placesList = this.workflowPlaces();
    });
  }

  detached() {
    this.resourceKindsChangedListener.dispose();
  }

  placesChanged(newValue: WorkflowPlace[], previousValue: WorkflowPlace[]) {
    if (newValue && previousValue && (diff(newValue, previousValue).length || diff(previousValue, newValue).length)) {
      this.publishValue();
    }
  }

  initialValueChanged() {
    this.places = this.getPlacesByPlaceIds(this.initialValue);
    if ((!this.places || !this.places.length) && !this.inputBoxFocused) {
      this.inputBoxVisible = false;
    }
    if (this.places && this.places.length) {
      this.inputBoxVisible = true;
    }
  }

  private getPlacesByPlaceIds(values: string[]): WorkflowPlace[] {
    const places = this.workflowPlaces();
    return places.filter(place => inArray(place.id, values));
  }

  workflowPlaces() {
    let places = [];
    this.resourceKinds.forEach(resourceKind => resourceKind.workflow && places.push(...resourceKind.workflow.places));
    return places;
  }

  toggleInputBoxVisibility() {
    if (this.inputBoxVisible) {
      if (this.places.length) {
        this.places = [];
        this.publishValueIfItChanged();
      }
      this.removeFocusFromInputBoxAndHideIt();
    } else {
      this.showInputBoxAndSetFocusOnIt();
    }
  }

  private publishValueIfItChanged() {
    if (this.places || undefined != this.initialValue) {
      this.publishValue();
    }
  }

  publishValue() {
    const filterValues = this.places.map(place => place.id);
    this.initialValue = filterValues;
    this.eventAggregator.publish('placeFilterValueChanged', filterValues);
  }
}
