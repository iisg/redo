import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached} from "aurelia-templating";
import {ResourceRepository} from "../../../resource-repository";
import {twoWay} from "../../../../common/components/binding-mode";
import {MetadataValue} from "../../../metadata-value";
import {BindingEngine} from "aurelia-binding";
import {ResourceLabelValueConverter} from "../../../details/resource-label-value-converter";
import {Resource} from "../../../resource";
import {ResourceSort} from "../../../resource-sort";
import {SystemMetadata} from "../../../../resources-config/metadata/system-metadata";
import {ResourceListQuery} from "../../../resource-list-query";

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
  resources: Resource[];
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
    if (this.selectedResources && this.selectedResources.length !== this.resourceIds.length) {
      this.updateResourceIds(this.selectedResources);
    }
  }

  selectedResourceChanged() {
    this.updateResourceIds(this.selectedResource ? [this.selectedResource] : []);
  }

  private updateResourceIds(newValues: Resource[]) {
    if (this.resourceIds) {
      this.resourceIds.splice(0, this.resourceIds.length);
      newValues.forEach(resource => this.resourceIds.push(new MetadataValue(resource.id)));
    }
  }

  private async initializeSelectedResources() {
    const selectedResources = this.resourceIds.map(metadataValue => {
      const resourceId = metadataValue.value;
      let resource = this.resources.find(resource => resource.id == resourceId);
      if (resource) {
        return Promise.resolve(resource);
      } else {
        return this.resourceRepository.getTeaser(resourceId)
          .then(teaser => {
            if (teaser) {
              return teaser;
            } else {
              resource = new Resource();
              resource.id = resourceId;
              this.resources.push(resource);
              return resource;
            }
          });
      }
    });
    Promise.all(selectedResources).then(resources => {
      if (this.multipleChoice) {
        this.selectedResources = resources;
      } else {
        this.selectedResource = resources[0];
      }
    });
  }

  private loadResources() {
    this.prepareQuery().sortByMetadataIds([new ResourceSort('id')]).get()
      .then(resources => {
        this.resources = resources;
        this.initializeSelectedResources();
        this.useDropdown = this.resources.length > this.SWITCH_TO_DROPDOWN;
        this.ready = true;
      });
  }

  private prepareQuery(itemsPerPage: number = 30, page: number = 1): ResourceListQuery {
    const query = this.resourceRepository.getTeaserListQuery()
      .setCurrentPageNumber(page)
      .setResultsPerPage(itemsPerPage);
    if (this.resourceClass) {
      query.filterByResourceClasses(this.resourceClass);
    }
    if (this.resourceKindIds) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter);
    }
    return query;
  }

  searchFunction(term, page): Promise<{ results, pagination: { more: boolean, itemsPerPage: number } }> {
    const itemsPerPage = 30;
    let labelFilter = {};
    labelFilter[SystemMetadata.RESOURCE_LABEL.id] = term != '' ? '^' + term.replace(/\s+/g, '.+') : '';
    this.contentsFilter = {...this.contentsFilter, ...labelFilter};
    return this.prepareQuery(itemsPerPage, page)
      .sortByMetadataIds([new ResourceSort('id')])
      .get().then(pageResult => ({
        results: pageResult,
        pagination: {more: itemsPerPage < pageResult.total, itemsPerPage: itemsPerPage}
      }));
  }

  formatter(item): { text: string } {
    return ({text: this.resourceLabel.toView(item)});
  }

  clearSelectedResource() {
    this.selectedResource = undefined;
    this.selectedResourceChanged();
  }
}
