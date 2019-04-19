import {autoinject, Container} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {ResourceRepository} from "../../../resource-repository";
import {twoWay} from "../../../../common/components/binding-mode";
import {MetadataValue} from "../../../metadata-value";
import {BindingEngine} from "aurelia-binding";
import {ResourceLabelValueConverter} from "../../../details/resource-label-value-converter";
import {Resource} from "../../../resource";
import {ResourceSort} from "../../../resource-sort";
import {SystemMetadata} from "../../../../resources-config/metadata/system-metadata";

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
  @bindable filters: Object = {};
  resources: Resource[] = [];
  ready: boolean = false;
  useDropdown: boolean = false;

  private readonly SWITCH_TO_DROPDOWN: number = 8;

  constructor(private resourceRepository: ResourceRepository,
              private bindingEngine: BindingEngine,
              private resourceLabel: ResourceLabelValueConverter) {
  }

  attached() {
    this.filters = {contentFilters: this.contentsFilter, resourceClass: this.resourceClass, resourceKindIds: this.resourceKindIds};
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
    query.sortByMetadataIds([new ResourceSort('id')]).get()
      .then(resources => {
        this.resources = resources;
        this.initializeSelectedResources();
        this.useDropdown = this.resources.length > this.SWITCH_TO_DROPDOWN;
        this.ready = true;
      });
  }

  searchFunction({term, page}, filters: Object): Promise<{ results, pagination: { more: boolean, itemsPerPage: number } }> {
    const itemsPerPage = 24;
    let labelFilter = {};
    labelFilter[SystemMetadata.RESOURCE_LABEL.id] = term != '' ? '^' + term.replace(/\s+/g, '.+') : '';
    const query = Container.instance.get(ResourceRepository).getTeaserListQuery();
    if (filters.hasOwnProperty('resourceClass')) {
      query.filterByResourceClasses(filters['resourceClass']);
    }
    if (filters.hasOwnProperty('resourceKindIds')) {
      query.filterByResourceKindIds(filters['resourceKindIds']);
    }
    if (filters.hasOwnProperty('contentFilters')) {
      labelFilter = {...filters['contentFilters'], ...labelFilter};
    }
    return query
      .filterByContents(labelFilter)
      .sortByMetadataIds([new ResourceSort('id')])
      .setResultsPerPage(itemsPerPage)
      .setCurrentPageNumber(page)
      .get().then(pageResult => ({
      results: pageResult,
      pagination: {more: itemsPerPage < pageResult.total, itemsPerPage: itemsPerPage}
    }));
  }

  formatter({item}): { text: string } {
    return ({text: Container.instance.get(ResourceLabelValueConverter).toView(item)});
  }
}
