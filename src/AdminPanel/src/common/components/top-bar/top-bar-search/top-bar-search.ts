import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {EventAggregator} from "aurelia-event-aggregator";
import {Router} from "aurelia-router";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {MetadataValue} from "resources/metadata-value";
import {propertyKeys, safeJsonParse} from "common/utils/object-utils";
import {ContextResourceClass, ResourceClassChangeEvent} from "resources/context/context-resource-class";
import {filterableControls} from "resources-config/metadata/metadata-control";
import {SystemMetadata} from "resources-config/metadata/system-metadata";

@autoinject
export class TopBarSearch {
  resourceClass: string;
  metadata: Metadata;
  metadataValue: MetadataValue = new MetadataValue();
  private searchData: StringMap<SearchData> = {};
  controls = filterableControls;
  classlessSearchableMetadataIds: number[] = [SystemMetadata.RESOURCE_LABEL.id];

  constructor(
    eventAggregator: EventAggregator,
    private router: Router,
    private metadataRepository: MetadataRepository
  ) {
    eventAggregator.subscribe(ContextResourceClass.CHANGE_EVENT,
      (event: ResourceClassChangeEvent) => {
        this.updateSearchData(event);
      });
  }

  attached() {
    const queryParams = this.router.currentInstruction.queryParams;
    const contentsFilter = safeJsonParse(queryParams['contentsFilter']);
    this.fetchMetadata(contentsFilter);
  }

  private fetchMetadata(contentsFilter: any) {
    const id = contentsFilter && propertyKeys(contentsFilter)[0];
    if (id) {
      this.metadataRepository.get(id).then(metadata => {
        this.metadata = metadata;
        this.metadataValue = new MetadataValue(contentsFilter[id]);
        this.setValueByResourceClass(this.resourceClass, this.metadata, this.metadataValue);
      });
    }
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
    const queryParams = this.router.currentInstruction.queryParams;
    if (this.metadata.name === 'ID') {
      queryParams['ids'] = this.metadataValue.value;
    } else {
      contentsFilter[this.metadata.id] = this.metadataValue.value;
      queryParams['contentsFilter'] = JSON.stringify(contentsFilter);
    }
    queryParams['resourceClass'] = this.resourceClass;
    queryParams['allLevels'] = true;
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
