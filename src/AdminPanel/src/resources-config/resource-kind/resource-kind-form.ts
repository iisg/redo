import {ResourceKind} from "./resource-kind";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentDetached} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {MetadataRepository} from "../metadata/metadata-repository";
import {BindingSignaler} from "aurelia-templating-resources";
import {Metadata} from "../metadata/metadata";
import {noop, VoidFunction} from "common/utils/function-utils";
import {removeValue} from "common/utils/array-utils";

@autoinject
export class ResourceKindForm implements ComponentDetached {
  @bindable submit: (value: {savedResourceKind: ResourceKind}) => Promise<any>;
  @bindable cancel: VoidFunction = noop;
  @bindable resourceClass: string;
  @bindable edit: ResourceKind;

  resourceKind: ResourceKind = new ResourceKind;
  baseMetadataMap: Map<Metadata, Metadata> = new Map();
  submitting = false;
  sortingMetadata = false;
  @bindable newMetadataBase: Metadata; // we only need @observable but it's buggy: https://github.com/aurelia/binding/issues/594

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
    const metadata = Metadata.createFromBase(this.newMetadataBase);
    this.baseMetadataMap.set(metadata, this.newMetadataBase);
    this.resourceKind.metadataList.push(metadata);
    this.newMetadataBase = undefined;
  }

  base(metadata: Metadata): Metadata {
    return this.baseMetadataMap.get(metadata);
  }

  removeMetadata(metadata: Metadata) {
    removeValue(this.resourceKind.metadataList, metadata);
  }

  isSortingMetadata() {
    return this.sortingMetadata;
  }

  @computedFrom('resourceKind.id')
  get editing(): boolean {
    return !!this.resourceKind.id;
  }

  editChanged() {
    this.resourceKind = new ResourceKind;
    if (this.edit) {
      this.resourceKind = ResourceKind.clone(this.edit);
      this.resourceKind.metadataList.forEach(metadata => {
        this.metadataRepository.getBase(metadata).then(baseMetadata => {
          this.baseMetadataMap.set(metadata, baseMetadata);
          metadata.clearInheritedValues(this.metadataRepository).then(() => this.signaler.signal('base-metadata-fetched'));
        });
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
          .then(() => this.editing || (this.resourceKind = new ResourceKind));
      }
    }).finally(() => this.submitting = false);
  }
}
