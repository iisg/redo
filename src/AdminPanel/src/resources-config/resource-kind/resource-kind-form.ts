import {ResourceKind} from "./resource-kind";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {MetadataRepository} from "../metadata/metadata-repository";
import {BindingSignaler} from "aurelia-templating-resources";
import {Metadata} from "../metadata/metadata";
import {noop, VoidFunction} from "common/utils/function-utils";
import {move, removeValue} from "common/utils/array-utils";
import {EntitySerializer} from "common/dto/entity-serializer";
import {SystemMetadata} from "../metadata/system-metadata";
import {Configure} from "aurelia-configuration";

@autoinject
export class ResourceKindForm implements ComponentAttached, ComponentDetached {
  @bindable submit: (value: { savedResourceKind: ResourceKind }) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable resourceClass: string;
  @bindable edit: ResourceKind;

  resourceKind: ResourceKind = new ResourceKind();
  originalMetadataList: Metadata[];
  submitting = false;
  sortingMetadata = false;
  @bindable metadataToAdd: Metadata; // we only need @observable but it's buggy: https://github.com/aurelia/binding/issues/594

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private signaler: BindingSignaler,
              private metadataRepository: MetadataRepository,
              private entitySerializer: EntitySerializer,
              private config: Configure) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  attached() {
    if (!this.edit) {
      this.resourceKind.metadataList.unshift(SystemMetadata.PARENT);
      this.resourceKind.resourceClass = this.resourceClass;
    }
  }

  async resourceClassChanged() {
    this.originalMetadataList = undefined;
    this.originalMetadataList = await this.metadataRepository.getListQuery()
      .filterByResourceClasses(this.resourceClass)
      .onlyTopLevel()
      .get();
  }

  metadataToAddChanged() {
    if (this.metadataToAdd) {
      const metadataOverrides = this.entitySerializer.clone(this.metadataToAdd);
      metadataOverrides.clearInheritedValues(this.metadataRepository, this.metadataToAdd);
      this.resourceKind.metadataList.push(metadataOverrides);
      this.metadataToAdd = undefined;
    }
  }

  originalMetadata(metadata: Metadata): Metadata {
    return this.originalMetadataList.find(m => m.id == metadata.id);
  }

  removeMetadata(metadata: Metadata) {
    removeValue(this.resourceKind.metadataList, metadata);
  }

  moveUp(metadata: Metadata) {
    move(this.resourceKind.metadataList, metadata, -1);
  }

  moveDown(metadata: Metadata) {
    move(this.resourceKind.metadataList, metadata, 1);
  }

  @computedFrom('resourceKind.id')
  get editing(): boolean {
    return !!this.resourceKind.id;
  }

  @computedFrom('resourceKind.metadataList', 'resourceKind.metadataList.length')
  get editableMetadataList(): Metadata[] {
    return this.resourceKind.metadataList.filter(metadata => metadata.id > 0);
  }

  get resourceChildConstraintMetadata(): Metadata {
    return this.resourceKind.metadataList.find(metadata => metadata.id === SystemMetadata.PARENT.id);
  }

  editChanged() {
    this.resourceKind = new ResourceKind();
    if (this.edit) {
      this.resourceKind = this.entitySerializer.clone(this.edit);
      this.resourceKind.metadataList.forEach(metadata => {
        metadata.clearInheritedValues(this.metadataRepository).then(() => this.signaler.signal('original-metadata-changed'));
      });
    }
  }

  detached() {
    this.sortingMetadata = false;
    this.edit = undefined;
  }

  validateAndSubmit() {
    this.submitting = true;
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({savedResourceKind: this.resourceKind}))
          .then(() => this.editing || (this.resourceKind = new ResourceKind()));
      }
    }).finally(() => this.submitting = false);
  }

  canRemoveMetadata(metadata: Metadata): boolean {
    if (this.resourceClass == 'users') {
      const mappedMetadataIds = this.config.get('user_mapped_metadata_ids') || [];
      return mappedMetadataIds.indexOf(metadata.id) === -1;
    }
    return true;
  }
}
