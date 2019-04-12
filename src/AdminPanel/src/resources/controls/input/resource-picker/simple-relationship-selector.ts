import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {ResourceRepository} from "../../../resource-repository";
import {twoWay} from "../../../../common/components/binding-mode";
import {MetadataValue} from "../../../metadata-value";
import {BindingEngine} from "aurelia-binding";
import {ResourceLabelValueConverter} from "../../../details/resource-label-value-converter";
import {Resource} from "../../../resource";
import {ResourceSort} from "../../../resource-sort";

@autoinject
export class SimpleRelationshipSelector implements ComponentAttached {
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

  private readonly SWITCH_TO_DROPDOWN: number = 8;

  constructor(private resourceRepository: ResourceRepository,
              private bindingEngine: BindingEngine,
              private resourceLabel: ResourceLabelValueConverter) {
  }

  attached() {
    this.loadResources();
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

  private initializeSelectedResources() {
    if (!this.multipleChoice && this.resourceIds.length) {
      this.selectedResource = this.resources.find(resource => resource.id == this.resourceIds[0].value);
    } else {
      let idValues = this.resourceIds.map(metadataValue => metadataValue.value);
      this.selectedResources = this.resources.filter(resource => idValues.includes(resource.id));
    }
  }

  private loadResources() {
    const query = this.resourceRepository.getTeaserListQuery();
    if (this.resourceClass) {
      query.filterByResourceClasses(this.resourceClass);
    }
    if (this.resourceKindIds) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter);
    }
    query.sortByMetadataIds([new ResourceSort('id')]);
    query.get().then(resources => {
      this.resources = resources;
      this.initializeSelectedResources();
      this.useDropdown = this.resources.length > this.SWITCH_TO_DROPDOWN;
      this.ready = true;
    });
  }
}
