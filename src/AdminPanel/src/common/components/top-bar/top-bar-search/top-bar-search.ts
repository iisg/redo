import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {Router} from "aurelia-router";
import {Metadata} from "../../../../resources-config/metadata/metadata";
import {MetadataRepository} from "../../../../resources-config/metadata/metadata-repository";
import {MetadataValue} from "../../../../resources/metadata-value";
import {propertyKeys, safeJsonParse} from "../../../utils/object-utils";
import {ContextResourceClass, ResourceClassChangeEvent} from "./../../../../resources/context/context-resource-class";

@autoinject
export class TopBarSearch {
  resourceClass: string;
  metadata: Metadata;
  metadataValue: MetadataValue = new MetadataValue();
  private searchData: StringMap<SearchData> = {};

  constructor(eventAggregator: EventAggregator, private router: Router, private metadataRepository: MetadataRepository) {
    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => this.updateSearchData(event));
  }

  attached() {
    const queryParams = this.router.currentInstruction.queryParams;
    const contentsFilter = safeJsonParse(queryParams['contentsFilter']);
    if (contentsFilter) {
      this.fetchMetadata(contentsFilter);
    }
  }

  private fetchMetadata(contentsFilter: any) {
    const id = propertyKeys(contentsFilter)[0];
    this.metadataRepository.get(id).then(metadata => {
      this.metadata = metadata;
      this.metadataValue = new MetadataValue(contentsFilter[id]);
      this.setValueByResourceClass(this.resourceClass, this.metadata, this.metadataValue);
    });
  }

  private updateSearchData(event: ResourceClassChangeEvent): void {
    this.resourceClass = event.newResourceClass;
    const searchData = this.getValueByResourceClass(this.resourceClass);
    this.metadata = searchData.metadata;
    this.metadataValue = searchData.metadataValue;
  }

  private setValueByResourceClass(resourceClass: string, metadata: Metadata, metadataValue: MetadataValue) {
    this.searchData[resourceClass] = new SearchData(metadataValue, metadata);
  }

  private getValueByResourceClass(resourceClass: string): SearchData {
    return this.searchData[resourceClass] || new SearchData(new MetadataValue());
  }

  findResources(): void {
    this.setValueByResourceClass(this.resourceClass, this.metadata, this.metadataValue);
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

export class SearchData {
  metadata: Metadata;
  metadataValue: MetadataValue;

  constructor(metadataValue?: MetadataValue, metadata?: Metadata) {
    this.metadata = metadata;
    this.metadataValue = metadataValue;
  }
}
