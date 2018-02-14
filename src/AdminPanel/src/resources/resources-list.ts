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

  constructor(private resourceRepository: ResourceRepository,
              private resourceKindRepository: ResourceKindRepository,
              private ea: EventAggregator) {
  }

  activate(params: any) {
    this.fetchList(params.resourceClass);
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
      this.fetchResourcesByParent();
    }
  }

  fetchResources() {
    this.progressBar = true;
    this.resourceRepository.getListQuery()
      .onlyTopLevel()
      .filterByResourceClasses(this.resourceClass)
      .get()
      .then(resources => {
        this.progressBar = false;
        this.resources = resources;
        this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined);
      });
  }

  fetchResourcesByParent() {
    this.resourceRepository.getListQuery()
      .filterByParentId(this.parentResource.id)
      .get()
      .then(resources => {
        this.resources = resources;
        if (!this.addFormOpened) {
          this.addFormOpened = (this.resources.length == 0) && (this.parentResource == undefined);
        }
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
