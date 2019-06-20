import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../../resource";
import {MetadataValue} from "../../metadata-value";
import {EntitySerializer} from "../../../common/dto/entity-serializer";
import {MetadataRepository} from "../../../resources-config/metadata/metadata-repository";
import {LocalStorage} from "common/utils/local-storage";

@autoinject
export class ResourceMetadataValueDisplay {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable value: MetadataValue;
  @bindable checkMetadataBrief: boolean = false;
  submetadataCollapsed: boolean = false;

  private static LOCAL_STORAGE_COLLAPSED_KEY = 'collapsedSubmetadata';

  private submetadataResource: Resource;

  constructor(private entitySerializer: EntitySerializer, private metadataRepository: MetadataRepository) {
  }

  attached() {
    this.submetadataCollapsed = LocalStorage.get(ResourceMetadataValueDisplay.LOCAL_STORAGE_COLLAPSED_KEY, {})[this.metadata.id] || false;
  }

  async valueChanged() {
    this.submetadataResource = this.entitySerializer.clone(this.resource, Resource.NAME);
    this.submetadataResource.kind.metadataList = [];
    if (this.hasSubmetadata) {
      this.submetadataResource.contents = this.value.submetadata || {};
      this.submetadataResource.kind.metadataList = await this.metadataRepository.getListQuery().filterByParentId(this.metadata.id).get();
    }
  }

  toggleMetadataVisibility(): void {
    this.submetadataCollapsed = !this.submetadataCollapsed;
    const setting = LocalStorage.get(ResourceMetadataValueDisplay.LOCAL_STORAGE_COLLAPSED_KEY, {});
    setting[this.metadata.id] = this.submetadataCollapsed;
    LocalStorage.set(ResourceMetadataValueDisplay.LOCAL_STORAGE_COLLAPSED_KEY, setting);
  }

  private get hasSubmetadata(): boolean {
    return this.value.submetadata && Object.keys(this.value.submetadata).length > 0;
  }

  private get isCollapsible(): boolean {
    return !this.metadata.parentId;
  }
}
