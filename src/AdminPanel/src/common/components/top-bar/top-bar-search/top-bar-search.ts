import {ContextResourceClass, ResourceClassChangeEvent} from './../../../../resources/context/context-resource-class';
import {Router} from "aurelia-router";
import {EventAggregator} from "aurelia-event-aggregator";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {MetadataValue} from "../../../../resources/metadata-value";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";

@autoinject
export class TopBarSearch {
  resourceClass: string;
  metadata: Metadata;
  metadataValue: MetadataValue = new MetadataValue();

  constructor(eventAggregator: EventAggregator, private router: Router) {
    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => this.updateResourceClass(event));
  }

  private updateResourceClass(event: ResourceClassChangeEvent): void {
    this.resourceClass = event.newResourceClass;
    this.metadata = undefined;
  }

  findResources(): void {
    let contentsFilter = {};
    contentsFilter[this.metadata.id] = this.metadataValue.value;
    const queryParams = this.router.currentInstruction.queryParams;
    queryParams['resourceClass'] = this.metadata.resourceClass;
    queryParams['contentsFilter'] = JSON.stringify(contentsFilter);
    this.router.navigateToRoute('resources', queryParams);
  }

  @computedFrom('metadata', 'metadataValue.value')
  get disabled(): boolean {
    return !this.metadata || !this.metadataValue.value;
  }
}
