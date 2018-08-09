import {computedFrom} from "aurelia-binding";
import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {BindingSignaler} from "aurelia-templating-resources";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {EntitySerializer} from "common/dto/entity-serializer";
import {move, removeValue} from "common/utils/array-utils";
import {noop, VoidFunction} from "common/utils/function-utils";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {Metadata} from "../metadata/metadata";
import {MetadataRepository} from "../metadata/metadata-repository";
import {SystemMetadata} from "../metadata/system-metadata";
import {ResourceKind} from "./resource-kind";
import {SystemResourceKinds} from "./system-resource-kinds";

@autoinject
export class ResourceKindForm extends ChangeLossPreventerForm implements ComponentAttached, ComponentDetached {
  @bindable submit: (value: { savedResourceKind: ResourceKind }) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable resourceClass: string;
  @bindable currentlyEditedResourceKind: ResourceKind;
  updateResourceKindMetadataChooserValues: () => void;
  resourceKind: ResourceKind = new ResourceKind();

  originalMetadataList: Metadata[];
  submitting = false;
  sortingMetadata = false;
  hasWorkflowChosen = false;
  @bindable metadataToAdd: Metadata; // we only need @observable but it's buggy: https://github.com/aurelia/binding/issues/594

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private signaler: BindingSignaler,
              private metadataRepository: MetadataRepository,
              private entitySerializer: EntitySerializer,
              private config: Configure,
              private changeLossPreventer: ChangeLossPreventer) {
    super();
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  attached() {
    if (!this.currentlyEditedResourceKind) {
      this.resourceKind.ensureHasSystemMetadata();
      this.resourceKind.resourceClass = this.resourceClass;
    }
    this.changeLossPreventer.enable(this);
  }

  async resourceClassChanged() {
    this.originalMetadataList = undefined;
    this.originalMetadataList = await this.metadataRepository.getListQuery()
      .filterByResourceClasses(this.resourceClass)
      .onlyTopLevel()
      .addSystemMetadataIds(SystemMetadata.RESOURCE_LABEL.id)
      .get();
  }

  metadataToAddChanged() {
    if (this.metadataToAdd) {
      const metadataOverrides = this.entitySerializer.clone(this.metadataToAdd);
      metadataOverrides.clearInheritedValues(this.metadataRepository, this.metadataToAdd);
      this.resourceKind.metadataList.push(metadataOverrides);
      this.metadataToAdd = undefined;
      this.updateResourceKindMetadataChooserValues();
    }
  }

  originalMetadata(metadata: Metadata): Metadata {
    return this.originalMetadataList.find(originalMetadata => originalMetadata.id == metadata.id);
  }

  removeMetadata(metadata: Metadata) {
    removeValue(this.resourceKind.metadataList, metadata);
    this.updateResourceKindMetadataChooserValues();
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
    return this.resourceKind.metadataList
      .filter(metadata => metadata.id != SystemMetadata.PARENT.id)
      .filter(metadata => metadata.id != SystemMetadata.REPRODUCTOR.id);
  }

  get resourceChildConstraintMetadata(): Metadata {
    return this.resourceKind.metadataList.find(metadata => metadata.id === SystemMetadata.PARENT.id);
  }

  currentlyEditedResourceKindChanged() {
    this.resourceKind = new ResourceKind();
    this.hasWorkflowChosen = false;
    if (this.currentlyEditedResourceKind) {
      this.resourceKind = this.entitySerializer.clone(this.currentlyEditedResourceKind);
      this.resourceKind.metadataList.forEach(metadata => {
        metadata.clearInheritedValues(this.metadataRepository).then(() => this.signaler.signal('original-metadata-changed'));
      });
      this.hasWorkflowChosen = !!this.resourceKind.workflow;
    }
  }

  detached() {
    this.sortingMetadata = false;
    this.currentlyEditedResourceKind = undefined;
  }

  validateAndSubmit() {
    this.submitting = true;
    $('.resource-kind-edit-form-metadata-item').removeClass('not-valid');
    this.controller.validate().then(result => {
      if (result.valid) {
        return Promise.resolve(this.submit({savedResourceKind: this.resourceKind}))
          .then(() => this.changeLossPreventer.enable(this))
          .then(() => this.editing || (this.resourceKind = new ResourceKind()));
      } else {
        $('.has-error').closest('.resource-kind-edit-form-metadata-item').addClass('not-valid');
      }
    }).finally(() => this.submitting = false);
  }

  canRemoveMetadata(metadata: Metadata): boolean {
    if (this.resourceKind.id == SystemResourceKinds.USER_ID) {
      const mappedMetadataIds = this.config.get('user_mapped_metadata_ids') || [];
      return metadata.id > 0 && mappedMetadataIds.indexOf(metadata.id) === -1;
    }
    return metadata.id != SystemMetadata.RESOURCE_LABEL.id;
  }

  cancelForm(): void {
    this.changeLossPreventer.canLeaveView().then(() => this.cancel());
  }
}
