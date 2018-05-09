import {bindable} from "aurelia-templating";
import {Metadata} from "resources-config/metadata/metadata";
import {autoinject} from "aurelia-dependency-injection";
import {booleanAttribute} from "common/components/boolean-attribute";
import {Resource} from "../resource";
import {ValidationController} from "aurelia-validation";
import {MetadataValue} from "../metadata-value";
import {MetadataRepository} from "../../resources-config/metadata/metadata-repository";
import {EntitySerializer} from "../../common/dto/entity-serializer";

@autoinject
export class ResourceMetadataValueInput {
  @bindable metadata: Metadata;
  @bindable resource: Resource;
  @bindable value: MetadataValue;
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable validationController: ValidationController;
  metadataInitialValue: MetadataValue;

  private submetadataResource: Resource;

  constructor(private metadataRepository: MetadataRepository, private entitySerializer: EntitySerializer) {
  }

  async valueChanged() {
    if (!this.value.submetadata) {
      this.value.submetadata = {};
    }
    this.metadataInitialValue = new MetadataValue(this.value.value);
    this.submetadataResource = this.entitySerializer.clone(this.resource, Resource.NAME);
    this.submetadataResource.contents = this.value.submetadata;
    this.submetadataResource.kind.metadataList = [];
    this.submetadataResource.kind.metadataList = await this.metadataRepository.getListQuery().filterByParentId(this.metadata.id).get();
  }
}
