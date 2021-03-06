import {ResourceListFilter} from "../resource-list-filter";
import {WorkflowPlace} from "workflows/workflow";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {bindable, ComponentBind, ComponentDetached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {diff, inArray} from "common/utils/array-utils";
import {BindingEngine, Disposable, observable} from "aurelia-binding";
import {FilterChangedEvent} from "../../resources-list-filters";

@autoinject
export class ResourceListPlaceFilter extends ResourceListFilter implements ComponentBind, ComponentDetached {
  @bindable resourceKinds: ResourceKind[];
  @bindable initialValue: string[];
  @bindable eventTarget: string;
  @observable places: WorkflowPlace[] = [];

  placesList: WorkflowPlace[] = [];

  private resourceKindsChangedListener: Disposable;

  constructor(private eventAggregator: EventAggregator, private bindingEngine: BindingEngine) {
    super();
  }

  bind() {
    this.placesList = this.workflowPlaces();
    if (this.initialValue && this.initialValue.length) {
      this.places = this.getPlacesByPlaceIds(this.initialValue);
      this.inputBoxVisible = true;
    }
    this.resourceKindsChangedListener = this.bindingEngine.collectionObserver(this.resourceKinds).subscribe(() => {
      this.placesList = this.workflowPlaces();
      this.places = this.places.filter(place => this.placesList.map(wf => wf.id).indexOf(place.id) != -1);
      this.publishValue();
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
    return this.placesList.filter(place => inArray(place.id, values));
  }

  workflowPlaces() {
    return this.resourceKinds
      .filter(rk => !!rk.workflow)
      .map(rk => rk.workflow)
      .filter((workflow, index, self) => self.map(wf => wf.id).indexOf(workflow.id) === index)
      .map(workflow => workflow.places.map(place => Object.assign({}, place, {workflow: workflow})))
      .reduce((allPlaces, singleWorkflowPlaces) => allPlaces.concat(singleWorkflowPlaces), []);
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
    this.eventAggregator.publish('placeFilterValueChanged', {
      value: filterValues,
      target: this.eventTarget
    } as FilterChangedEvent<PlacesFilterChange>);
  }
}

export type PlacesFilterChange = string[];
