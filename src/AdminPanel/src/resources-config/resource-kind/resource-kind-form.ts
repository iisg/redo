import {ResourceKind} from "./resource-kind";
import {BootstrapSelectChangeEvent} from "../bootstrap-select/bootstrap-select";
import {Metadata, ResourceKindMetadata} from "../metadata/metadata";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentDetached} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {deepCopy} from "../../common/utils/object-utils";

@autoinject
export class ResourceKindForm implements ComponentDetached {
  @bindable submit: (value: {savedResourceKind: ResourceKind}) => Promise<any>;
  @bindable cancel: () => any = () => undefined;
  @bindable edit: ResourceKind;

  resourceKind: ResourceKind = new ResourceKind;
  submitting = false;
  sortingMetadata = false;

  private controller: ValidationController;

  constructor(validationControllerFactory: ValidationControllerFactory) {
    this.controller = validationControllerFactory.createForCurrentScope();
    this.controller.addRenderer(new BootstrapValidationRenderer);
  }

  addNewMetadata(event: BootstrapSelectChangeEvent<Metadata>) {
    let metadata = new ResourceKindMetadata;
    metadata.base = event.detail.value;
    this.resourceKind.metadataList.push(metadata);
  }

  removeMetadata(metadata: ResourceKindMetadata) {
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
      this.clearInheritedValues();
    }
  }

  private clearInheritedValues() {
    for (let metadata of this.resourceKind.metadataList) {
      for (let overridableField of ['label', 'placeholder', 'description']) {
        for (let languageCode in metadata[overridableField]) {
          if (metadata[overridableField][languageCode] == metadata.base[overridableField][languageCode]) {
            metadata[overridableField][languageCode] = '';
          }
        }
      }
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
