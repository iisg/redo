import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {NavigationInstruction} from "aurelia-router";
import {DefaultBreadcrumbsProvider} from "./default-breadcrumbs-provider";
import {MetadataBreadcrumbsProvider} from "./metadata-breadcrumbs-provider";
import {ResourceBreadcrumbsProvider} from "./resource-breadcrumbs-provider";
import {ResourceKindBreadcrumbsProvider} from './resource-kind-breadcrumbs-provider';
import {WorkflowBreadcrumbsProvider} from "./workflow-breadcrumbs-provider";

@autoinject
export class Breadcrumbs {
  loading: boolean = false;

  private breadcrumbs: any;
  private providers: StringMap<BreadcrumbsProvider> = {};

  constructor(eventAggregator: EventAggregator,
              defaultBreadcrumbsProvider: DefaultBreadcrumbsProvider,
              resource: ResourceBreadcrumbsProvider,
              metadata: MetadataBreadcrumbsProvider,
              workflow: WorkflowBreadcrumbsProvider,
              resourceKind: ResourceKindBreadcrumbsProvider) {
    eventAggregator.subscribe('router:navigation:success',
      (event: { instruction: NavigationInstruction }) => this.updateBreadcrumbs(event.instruction));
    this.providers = {'default': defaultBreadcrumbsProvider, resource, metadata, workflow, resourceKind};
  }

  async updateBreadcrumbs(navigationInstruction: NavigationInstruction) {
    this.loading = true;
    const settings = navigationInstruction.config.settings;
    const provider = settings && settings.breadcrumbsProvider || 'default';
    this.breadcrumbs = await this.providers[provider].getBreadcrumbs(navigationInstruction);
    this.loading = false;
  }
}

export interface BreadcrumbsProvider {
  getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]>;
}

export class BreadcrumbItem {
  label: string;
  labelHtml?: boolean = false;
  route?: string;
  params?: StringMap<any>;
  replace?: boolean;
}
