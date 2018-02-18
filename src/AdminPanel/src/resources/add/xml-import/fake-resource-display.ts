import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {booleanAttribute} from "common/components/boolean-attribute";
import {Resource} from "../../resource";
import {computedFrom} from "aurelia-binding";
import {inArray} from "common/utils/array-utils";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {I18N} from "aurelia-i18n";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ResourceKind} from "../../../resources-config/resource-kind/resource-kind";

@autoinject
export class FakeResourceDisplay {
  @bindable values: StringMap<any[]>;
  @bindable metadataList: Metadata[];
  @bindable @booleanAttribute omitMissing: boolean = false;
  @bindable resourceKind: ResourceKind;
  @bindable resourceClass: string;

  resource = new Resource();
  pendingRequest: boolean = false;
  allMetadata: Metadata[] = undefined;

  constructor(private metadataRepository: MetadataRepository, private i18n: I18N, private entitySerializer: EntitySerializer) {
  }

  valuesChanged(): void {
    this.resource.contents = this.values;
  }

  resourceKindChanged() {
    this.resource.kind = this.resourceKind;
  }

  @computedFrom('metadataList', 'values', 'allMetadata')
  get fakeMetadataList(): Metadata[] {
    const unknownMetadataIds = this.getUnknownMetadataIds(this.metadataList);
    let result: Metadata[];
    if (unknownMetadataIds.length == 0) {
      result = this.metadataList;
    } else if (this.allMetadata !== undefined) {
      const extraMetadata = this.allMetadata.filter(metadata => inArray(metadata.id + '', unknownMetadataIds));
      result = this.addNecessaryFakeMetadata(this.metadataList.concat(extraMetadata));
    } else {
      if (!this.pendingRequest) {
        this.pendingRequest = true;
        this.metadataRepository.getListQuery()
          .filterByResourceClasses(this.resourceClass)
          .onlyTopLevel()
          .get()
          .then(allMetadata => this.allMetadata = allMetadata)
          .finally(() => this.pendingRequest = false);
      }
      result = this.addNecessaryFakeMetadata(this.metadataList);
    }
    return this.sanitizeControls(result);
  }

  private getUnknownMetadataIds(knownMetadata: Metadata[]): string[] {
    const knownMetadataIds = knownMetadata.map(metadata => metadata.id + '');
    return Object.keys(this.values).filter(id => !inArray(id, knownMetadataIds));
  }

  private createFakeMetadata(ids: string[]): Metadata[] {
    return ids.map(id => {
      const metadata = new Metadata();
      metadata.id = id as any;
      metadata.label = {dummyLanguage: this.getFakeLabel(id)};
      return metadata;
    });
  }

  private getFakeLabel(id: string) {
    return (id.match(/^\d+$/))
      ? this.i18n.tr("entity_types::metadata") + ' #' + id
      : id;
  }

  private addNecessaryFakeMetadata(metadataList: Metadata[]): Metadata[] {
    const unknownIds = this.getUnknownMetadataIds(metadataList);
    return metadataList.concat(this.createFakeMetadata(unknownIds));
  }

  @computedFrom('fakeMetadataList')
  get filteredMetadataList(): Metadata[] {
    return this.fakeMetadataList.filter(metadata => (metadata.id + '') in this.values);
  }

  private sanitizeControls(metadataList: Metadata[]): Metadata[] {
    metadataList = metadataList.map(metadata => this.entitySerializer.clone(metadata));
    for (const metadata of metadataList) {
      if (!inArray(metadata.control, ['text', 'textarea'])) {
        metadata.control = 'text';
      }
    }
    return metadataList;
  }
}
