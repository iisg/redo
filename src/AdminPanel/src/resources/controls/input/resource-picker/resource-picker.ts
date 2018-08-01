import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {observable} from "aurelia-binding";
import {twoWay} from "common/components/binding-mode";
import {ResourceRepository} from "resources/resource-repository";
import {Resource} from "resources/resource";
import {SystemMetadata} from './../../../../resources-config/metadata/system-metadata';
import {ResourceLabelValueConverter} from './../../../details/resource-label-value-converter';

@autoinject
export class ResourcePicker implements ComponentAttached {
  @bindable(twoWay) resourceId: number;
  @bindable resourceKindIds: number[] = [];
  @bindable contentsFilter: NumberMap<string> = {};
  @bindable resourceClass: string;
  @bindable disabled: boolean = false;

  @observable value: Resource;

  initialized: boolean = false;
  resources: Array<Resource> = [];
  invalidValue: boolean = false;

  // cannot be less than 5: https://www.yiiframework.com/forum/index.php/topic/73474-select2-infinite-scroll-stuck-on-first-page/
  private readonly RESULTS_PER_DROPDOWN_PAGE = 20;

  constructor(private resourceRepository: ResourceRepository, private resourceLabelValueConverter: ResourceLabelValueConverter) {
  }

  attached() {
    this.initialized = true;
  }

  searchFunction(term: string, page: number) {
    if (page === 1) {
      this.resources = [];
    }
    const searchContents: NumberMap<any> = {};
    searchContents[SystemMetadata.RESOURCE_LABEL.id] = term;
    return this.createQuery()
      .setResultsPerPage(this.RESULTS_PER_DROPDOWN_PAGE)
      .setCurrentPageNumber(page)
      .filterByContents(searchContents)
      .get()
      .then(result => {
        this.resources.push(...result as Array<Resource>);
        return result;
      })
      .then(result => {
        const morePages: boolean = this.resources.length < result.total;
        return {results: result as Array<any>, pagination: {more: morePages, itemsPerPage: this.RESULTS_PER_DROPDOWN_PAGE}};
      });
  }

  formatDropdownItem(resource: Resource) {
    const label = this.resourceLabelValueConverter.toView(resource);
    return {text: `<strong>${label}</strong>`};
  }

  private createQuery() {
    const query = this.resourceRepository.getListQuery();
    if (this.resourceKindIds.length > 0) {
      query.filterByResourceKindIds(this.resourceKindIds);
    }
    if (this.resourceClass) {
      query.filterByResourceClasses(this.resourceClass);
    }
    if (this.contentsFilter) {
      query.filterByContents(this.contentsFilter);
    }
    return query;
  }

  valueChanged(newValue: Resource) {
    if (!this.initialized) {
      return;
    }
    if (newValue == undefined) {
      this.resourceId = undefined;
    } else if (newValue.id != this.resourceId) {
      this.resourceId = newValue.id;
    }
  }

  resourceIdChanged(newResourceId: number) {
    this.invalidValue = false;
    if (newResourceId == undefined) {
      this.value = undefined;
      return;
    }
    if (!this.value || newResourceId != this.value.id) {
      const value = this.findResourceById(newResourceId);
      if (value == undefined) {
        this.invalidValue = (this.resources != undefined);
      }
      else {
        this.value = value;
      }
    }
  }

  findResourceById(id: number): Resource {
    if (this.resources == undefined) {
      return undefined;
    }
    for (let i = 0; i < this.resources.length; i++) {
      const resource = this.resources[i];
      if (resource.id == id) {
        return resource;
      }
    }
    return undefined;
  }
}
