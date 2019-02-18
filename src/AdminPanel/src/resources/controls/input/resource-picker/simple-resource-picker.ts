import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {ResourceRepository} from "../../../resource-repository";
import {twoWay} from "../../../../common/components/binding-mode";
import {MetadataValue} from "../../../metadata-value";
import {BindingEngine, Disposable} from "aurelia-binding";
import {ResourceLabelValueConverter} from "../../../details/resource-label-value-converter";
import {Resource} from "../../../resource";

@autoinject
export class SimpleResourcePicker implements ComponentAttached, ComponentDetached {
  @bindable(twoWay) resourceIds: MetadataValue[];
  @bindable(twoWay) selectedResources: Resource[] = [];
  @bindable(twoWay) selectedResource: Resource = undefined;
  @bindable resourceKindIds: number[] = [];
  @bindable contentsFilter: NumberMap<string> = {};
  @bindable resourceClass: string;
  @bindable disabled: boolean = false;
  @bindable multipleChoice: boolean = false;
  resources: Resource[] = [];
  ready: boolean = false;
  useDropdown: boolean = false;

  private resourceIdsSubscription: Disposable;
  private readonly SWITCH_TO_DROPDOWN: number = 8;

  constructor(private resourceRepository: ResourceRepository,
              private bindingEngine: BindingEngine,
              private resourceLabel: ResourceLabelValueConverter) {
  }

  attached() {
    this.loadResources();
  }

  detached(): void {
    this.disposeResourceIdsSubscription();
  }

  selectedResourcesChanged() {
    if (this.resourceIds && this.selectedResources && this.selectedResources.length !== this.resourceIds.length) {
      this.updateResourceIds(this.selectedResources);
    }
  }

  selectedResourceChanged() {
    if (this.resourceIds) {
      this.updateResourceIds(this.selectedResource ? [this.selectedResource] : []);
    }
  }

  private updateResourceIds(newValues: Resource[]) {
    this.resourceIds.splice(0, this.resourceIds.length);
    newValues.forEach(resource => this.resourceIds.push(new MetadataValue(resource.id)));
  }

  private observeResourceIds() {
    this.disposeResourceIdsSubscription();
    this.resourceIdsSubscription = this.bindingEngine.collectionObserver(this.resourceIds).subscribe(() => {
      this.updateSelectedResources();
    });
  }

  private disposeResourceIdsSubscription() {
    if (this.resourceIdsSubscription !== undefined) {
      this.resourceIdsSubscription.dispose();
      this.resourceIdsSubscription = undefined;
    }
  }

  private updateSelectedResources() {
    if (!this.multipleChoice) {
      this.selectedResource = this.resourceIds.length
        ? this.resources.find(resource => resource.id == this.resourceIds[0].value)
        : undefined;
    } else if (this.selectedResources.length !== this.resourceIds.length) {
      let idValues = this.resourceIds.map(metadataValue => metadataValue.value);
      this.selectedResources = this.resources.filter(resource => idValues.includes(resource.id));
    }
  }

  private loadResources() {
    const query = this.resourceRepository.getListQuery();
    if (this.resourceClass) {
      query.filterByResourceClasses(this.resourceClass);
    }
    if (this.resourceKindIds) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter);
    }
    query.get().then(resources => {
      this.resources = resources;
      this.updateSelectedResources();
    }).then(() => {
      this.observeResourceIds();
      this.useDropdown = this.resources.length > this.SWITCH_TO_DROPDOWN;
      this.ready = true;
    });
  }
}
