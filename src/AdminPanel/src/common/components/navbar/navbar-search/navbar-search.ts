import {NavigationInstruction, Router} from "aurelia-router";
import {EventAggregator} from "aurelia-event-aggregator";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {MetadataValue} from "../../../../resources/metadata-value";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {DefaultNavbarSearchProvider} from "./default-navbar-search-provider";
import {MetadataNavbarSearchProvider} from "./metadata-navbar-search-provider";
import {WorkflowNavbarSearchProvider} from "./workflow-navbar-search-provider";
import {UserNavbarSearchProvider} from "./user-navbar-search-provider";
import {ResourceNavbarSearchProvider} from "./resource-navbar-search-provider";

@autoinject
export class NavbarSearch {
  resourceClass: string;
  metadata: Metadata;
  metadataValue: MetadataValue = new MetadataValue();
  private providers: StringMap<NavbarSearchProvider> = {};

  constructor(eventAggregator: EventAggregator, private router: Router,
              defaultNavbarSearchProvider: DefaultNavbarSearchProvider, resource: ResourceNavbarSearchProvider,
              metadata: MetadataNavbarSearchProvider, workflow: WorkflowNavbarSearchProvider, user: UserNavbarSearchProvider) {
    eventAggregator.subscribe("router:navigation:success",
      (event: {instruction: NavigationInstruction}) => this.changeResourceClass(event.instruction));
    this.providers = {'default': defaultNavbarSearchProvider, resource, metadata, workflow, user};
  }

  async changeResourceClass(navigationInstruction: NavigationInstruction) {
    const provider = navigationInstruction.config.settings.navbarSearchProvider || 'default';
    const newResourceClass = await this.providers[provider].getResourceClass(navigationInstruction);
    if (newResourceClass !== this.resourceClass) {
      this.resourceClass = newResourceClass;
      this.metadata = undefined;
    }
  }

  findResources() {
    let contentsFilter = {};
    contentsFilter[this.metadata.id] = this.metadataValue.value;
    contentsFilter = JSON.stringify(contentsFilter);
    const urlDetail = this.router.generate('resources', {resourceClass: this.resourceClass, contentsFilter});
    this.router.navigate(urlDetail);
  }

  @computedFrom('metadata', 'metadataValue.value')
  get disabled(): boolean {
    return !this.metadata || !this.metadataValue.value;
  }
}

export interface NavbarSearchProvider {
  getResourceClass(navigationInstruction: NavigationInstruction): Promise<string>;
}
