import {Resource} from "../resource";
import {Validator} from "aurelia-validation";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ImportDialog} from "./xml-import/import-dialog";
import {Modal} from "common/dialog/modal";
import {ImportConfirmationDialog, ImportConfirmationDialogModel} from "./xml-import/import-confirmation-dialog";
import {inArray} from "common/utils/array-utils";
import {ImportResult} from "./xml-import/xml-import-client";

@autoinject
export class ResourceForm {
  @bindable submit: (value: { savedResource: Resource }) => Promise<any>;
  @bindable edit: Resource;
  @bindable parent: Resource;
  @bindable resourceClass: string;

  resource: Resource = new Resource();
  submitting: boolean = false;
  errorToDisplay: string;

  constructor(private validator: Validator, private entitySerializer: EntitySerializer, private modal: Modal) {
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  resourceClassChanged() {
    this.resource.resourceClass = this.resourceClass;
  }

  editChanged(newValue: Resource) {
    this.resource = this.entitySerializer.clone(newValue);
    this.resourceClass = this.resource.resourceClass;
    this.parentChanged(this.parent);
  }

  parentChanged(newParent: Resource) {
    if (newParent != undefined) {
      this.resource.contents[SystemMetadata.PARENT.baseId] = [newParent.id];
    }
  }

  validateAndSubmit() {
    this.submitting = true;
    this.errorToDisplay = undefined;
    this.validator.validateObject(this.resource).then(results => {
      const errors = results.filter(result => !result.valid);
      if (errors.length == 0) {
        return this.submit({savedResource: this.resource})
          .then(() => this.editing || (this.resource = new Resource));
      } else {
        this.errorToDisplay = errors[0].message;
      }
    }).finally(() => this.submitting = false);
  }

  openImportDialog() {
    this.modal.open(ImportDialog, {resourceKind: this.resource.kind}).then((importResult: ImportResult) => {
      const model: ImportConfirmationDialogModel = {
        metadataList: this.resource.kind.metadataList,
        importResult,
        invalidMetadataKeys: importResult.invalidMetadataKeys,
        resourceClass: this.resourceClass,
      };
      return this.modal.open(ImportConfirmationDialog, model);
    }).then(valueMap => this.importValues(valueMap));
  }

  importValues(valueMap: StringArrayMap): void {
    const metadataIds = this.resource.kind.metadataList.map(metadata => metadata.baseId + '');
    for (const metadataId in valueMap) {
      if (!inArray(metadataId + '', metadataIds)) {
        continue;
      }
      const importedValues = valueMap[metadataId];
      const currentValues = this.resource.contents[metadataId];
      for (const value of importedValues) {
        if (!inArray(value, currentValues)) {
          currentValues.push(value);
        }
      }
    }
  }
}
