import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ResourceBreadcrumbsProvider} from "./resource-breadcrumbs-provider";
import {DefaultBreadcrumbsProvider} from "./default-breadcrumbs-provider";
import {MetadataBreadcrumbsProvider} from "./metadata-breadcrumbs-provider";
import {WorkflowBreadcrumbsProvider} from "./workflow-breadcrumbs-provider";

@autoinject
export class Breadcrumbs {
  loading: boolean = false;

  private breadcrumbs: any;
  private providers: StringMap<BreadcrumbsProvider> = {};

  constructor(eventAggregator: EventAggregator,
              defaultBreadcrumbsProvider: DefaultBreadcrumbsProvider, resource: ResourceBreadcrumbsProvider,
              metadata: MetadataBreadcrumbsProvider, workflow: WorkflowBreadcrumbsProvider) {
    eventAggregator.subscribe("router:navigation:success",
      (event: { instruction: NavigationInstruction }) => this.updateBreadcrumbs(event.instruction));
    this.providers = {'default': defaultBreadcrumbsProvider, resource, metadata, workflow};
  }

  async updateBreadcrumbs(navigationInstruction: NavigationInstruction) {
    this.loading = true;
    const provider = navigationInstruction.config.settings.breadcrumbsProvider || 'default';
    this.breadcrumbs = await this.providers[provider].getBreadcrumbs(navigationInstruction);
    this.loading = false;
  }
}

export interface BreadcrumbsProvider {
  getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]>;
}

export class BreadcrumbItem {
  label: string;
  route?: string;
  params?: StringMap<any>;
  replace?: boolean;
}
