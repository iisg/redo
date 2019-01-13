import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {BindingEngine} from "aurelia-binding";
import {Disposable} from "aurelia-binding";
import {observable} from "aurelia-binding";

interface ChildResourceData {
  id: string;
  label: string;
}

@autoinject
export class ChildResourcesGroup {
  @bindable resourceId: string;
  @bindable resourceLabel: string;
  @bindable name: string;
  @bindable stringifiedChildResourcesData: string;
  @bindable stringifiedParentFilters: string;

  @observable checked: boolean;

  expanded: boolean;
  childResourcesData: ChildResourceData[];
  parentFilters: string[];
  selectedChildrenIds: string[];

  private childResourcesIds: string[];
  private selectedChildrenIdsSubscription: Disposable;
  private toggledManually: boolean;

  constructor(private bindingEngine: BindingEngine) {
  }

  bind() {
    if (this.stringifiedChildResourcesData) {
      this.childResourcesData = JSON.parse(this.stringifiedChildResourcesData);
      this.childResourcesIds = this.childResourcesData.map(childResourceData => childResourceData.id);
    }
    if (this.stringifiedParentFilters) {
      this.parentFilters = JSON.parse(this.stringifiedParentFilters);
      this.toggledManually = true;
      this.checked = this.parentFilters.includes(this.resourceId);
      if (this.childResourcesIds) {
        if (this.checked) {
          this.selectedChildrenIds = this.childResourcesIds.slice();
        } else {
          this.selectedChildrenIds = this.parentFilters.filter(parentFilter => this.childResourcesIds.includes(parentFilter));
        }
        if (this.selectedChildrenIds.length) {
          this.expanded = true;
        }
      }
    }
    if (this.childResourcesData) {
      if (!this.stringifiedParentFilters) {
        this.selectedChildrenIds = [];
      }
      this.beginSelectedChildrenIdsSubscription();
    }
  }

  beginSelectedChildrenIdsSubscription() {
    this.selectedChildrenIdsSubscription = this.bindingEngine.collectionObserver(this.selectedChildrenIds).subscribe(() => {
      let allChildrenSelected = this.selectedChildrenIds.length == this.childResourcesData.length;
      if (allChildrenSelected != this.checked) {
        this.toggledManually = true;
        this.checked = allChildrenSelected;
      }
    });
  }

  private disposeSelectedChildrenIdsSubscription() {
    if (this.selectedChildrenIdsSubscription) {
      this.selectedChildrenIdsSubscription.dispose();
    }
  }

  checkedChanged() {
    if (!this.toggledManually) {
      if (this.childResourcesIds) {
        this.disposeSelectedChildrenIdsSubscription();
        if (this.checked) {
          this.selectedChildrenIds = this.childResourcesIds.slice();
          this.expanded = true;
        } else {
          this.selectedChildrenIds = [];
        }
        this.beginSelectedChildrenIdsSubscription();
      }
    } else {
      this.toggledManually = false;
    }
  }

  unbind() {
    this.disposeSelectedChildrenIdsSubscription();
  }
}
