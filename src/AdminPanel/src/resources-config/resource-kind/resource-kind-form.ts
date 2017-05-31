import {ResourceKind} from "./resource-kind";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentDetached} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {deepCopy} from "common/utils/object-utils";
import {MetadataRepository} from "../metadata/metadata-repository";
import {BindingSignaler} from "aurelia-templating-resources";
import {Metadata} from "../metadata/metadata";

@autoinject
export class ResourceKindForm implements ComponentDetached {
  @bindable submit: (value: {savedResourceKind: ResourceKind}) => Promise<any>;
  @bindable cancel: () => any = () => undefined;
  @bindable edit: ResourceKind;

  resourceKind: ResourceKind = new ResourceKind;
  baseMetadataMap: Map<Metadata, Metadata> = new Map();
  submitting = false;
  sortingMetadata = false;
  @bindable newMetadataBase: Metadata; // actually we only need @observable but for some reason setting it to undefined doesn't propagate up

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory,
              private signaler: BindingSignaler,
              private metadataRepository: MetadataRepository) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  newMetadataBaseChanged() {
    if (this.newMetadataBase == undefined) {
      return;
    }
    let metadata = new Metadata;
    this.baseMetadataMap.set(metadata, this.newMetadataBase);
    const baseMetadata = this.base(metadata);
    metadata.baseId = baseMetadata.id;
    metadata.control = baseMetadata.control;
    metadata.constraints = deepCopy(baseMetadata.constraints);
    this.resourceKind.metadataList.push(metadata);
    this.newMetadataBase = undefined;
  }

  base(metadata: Metadata): Metadata {
    return this.baseMetadataMap.get(metadata);
  }

  removeMetadata(metadata: Metadata) {
    this.resourceKind.metadataList.splice(this.resourceKind.metadataList.indexOf(metadata), 1);
  }

  isSortingMetadata() {
    return this.sortingMetadata;
  }

  @computedFrom('resourceKind.id')
  get editing(): boolean {
    return !!this.resourceKind.id;
  }

  editChanged(newValue: ResourceKind) {
    this.resourceKind = new ResourceKind;
    if (newValue) {
      this.resourceKind = $.extend(this.resourceKind, deepCopy(newValue));
      this.resourceKind.metadataList.forEach(metadata => {
        this.metadataRepository.getBase(metadata).then(baseMetadata => {
          this.baseMetadataMap.set(metadata, baseMetadata);
          this.clearInheritedValues(metadata);
        });
      });
    }
  }

  private clearInheritedValues(metadata: Metadata) {
    for (let overridableField of ['label', 'placeholder', 'description']) {
      for (let languageCode in metadata[overridableField]) {
        if (metadata[overridableField][languageCode] == this.base(metadata)[overridableField][languageCode]) {
          metadata[overridableField][languageCode] = '';
        }
      }
    }
    this.signaler.signal('base-metadata-fetched');
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
          .then(() => this.editing || (this.resourceKind = new ResourceKind));
      }
    }).finally(() => this.submitting = false);
  }
}
