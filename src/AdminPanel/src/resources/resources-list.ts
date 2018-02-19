import {autoinject} from "aurelia-dependency-injection";
import {ResourceRepository} from "./resource-repository";
import {Resource} from "./resource";
import {bindable, ComponentAttached} from "aurelia-templating";
import {bindingMode, observable} from "aurelia-binding";
import {Metadata} from "../resources-config/metadata/metadata";
import {ResourceKindRepository} from "../resources-config/resource-kind/resource-kind-repository";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";
import {NavigationInstruction} from "aurelia-router";
import {EventAggregator, Subscription} from "aurelia-event-aggregator";
import {PageResult} from "./page-result";
import {ContextResourceClass} from "./context/context-resource-class";
import {ResourceMetadataSort} from "./resource-metadata-sort";

@autoinject
export class ResourcesList implements ComponentAttached {
  @bindable parentResource: Resource = undefined;
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasResources: boolean = undefined;

  @bindable resourceClass: string;
  @observable resources: PageResult<Resource>;
  page: number = 1;
  resultsPerPage: number = 10;
  addFormOpened: boolean;
  briefMetadata: Metadata[];
  progressBar: boolean;
  urlListener: Subscription;
  sortBy: ResourceMetadataSort[] = [];
  contentsFilter: NumberMap<string>;

  constructor(private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private ea: EventAggregator,
              private contextResourceClass: ContextResourceClass) {
  }

  activate(params: any) {
    this.contextResourceClass.setCurrent(params.resourceClass);
    this.contentsFilter = this.getParamsByName(params, 'contentsFilter');
    this.sortBy = this.getParamsByName(params, 'sortBy');
    this.fetchList(params.resourceClass);
  }

  private getParamsByName(params: any, key: string) {
    if (params[key]) {
      try {
        return JSON.parse(params[key]);
      } catch (e) {
        console.warn(e);  // tslint:disable-line
      }
    }
    return [];
  }

  private fetchList(resourceClass: string) {
    this.resourceClass = resourceClass;
    if (this.resources) {
      this.resources = new PageResult<Resource>();
    }
    this.addFormOpened = false;
    this.briefMetadata = [];
    this.fetchBriefMetadata();
    this.fetchResources();
  }

  bind() {
    this.urlListener = this.ea.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => {
        const newResourceClass = event.instruction.queryParams.resourceClass;
        if (newResourceClass && newResourceClass != this.resourceClass) {
          this.fetchList(newResourceClass);
        }
      });
  }

  unbind() {
    this.urlListener.dispose();
  }

  attached() {
    if (this.parentResource) {
      this.resourceClass = this.parentResource.resourceClass;
      this.fetchBriefMetadata();
      this.fetchResources();
    }
  }

  fetchResources() {
    this.progressBar = true;
    let query = this.resourceRepository.getListQuery();
    query = this.contentsFilter ? query.filterByContents(this.contentsFilter) : query;
    query = this.parentResource
      ? query.filterByParentId(this.parentResource.id)
      : query.onlyTopLevel()
      .filterByResourceClasses(this.resourceClass)
      .sortByMetadataIds(this.sortBy);
    query.get()
      .then(resources => {
        this.progressBar = false;
        this.resources = resources;
        this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined) && !this.contentsFilter;
      });
  }

  fetchBriefMetadata() {
    this.resourceKindRepository.getListByClass(this.resourceClass).then(resourceKindList => {
      this.briefMetadata = getMergedBriefMetadata(resourceKindList);
    });
  }

  resourcesChanged(newResources: Resource[]) {
    this.hasResources = newResources.length > 0;
  }

  addNewResource(resource: Resource): Promise<Resource> {
    resource.resourceClass = this.resourceClass;
    return this.resourceRepository.post(resource).then(resource => {
      this.addFormOpened = false;
      this.resources.push(resource);
      return resource;
    });
  }
}
