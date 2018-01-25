import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "../../resource";
import {MetadataValue} from "../../metadata-value";
import {EntitySerializer} from "../../../common/dto/entity-serializer";
import {MetadataRepository} from "../../../resources-config/metadata/metadata-repository";

@autoinject
export class ResourceMetadataValueDisplay {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable value: MetadataValue;

  private submetadataResource: Resource;

  constructor(private entitySerializer: EntitySerializer, private metadataRepository: MetadataRepository) {
  }

  async valueChanged() {
    this.submetadataResource = this.entitySerializer.clone(this.resource, Resource.NAME);
    this.submetadataResource.contents = this.value.submetadata || {};
    this.submetadataResource.kind.metadataList = [];
    this.submetadataResource.kind.metadataList = await this.metadataRepository.getByParent(this.metadata);
  }
}
