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
import {ImportResult} from "./xml-import/xml-import-client";
import {inArray, flatten} from "common/utils/array-utils";
import {WorkflowTransition, RequirementState} from "../../workflows/workflow";
import {Router} from "aurelia-router";
import {numberKeysByValue} from "../../common/utils/object-utils";

@autoinject
export class ResourceForm {
  @bindable resourceClass: string;
  @bindable parent: Resource;
  @bindable edit: Resource;
  @bindable submit: (value: {savedResource: Resource, transitionId: string}) => Promise<any>;
  resource: Resource = new Resource();
  submitting: boolean = false;
  errorToDisplay: string;
  transition: WorkflowTransition;

  constructor(private validator: Validator, private entitySerializer: EntitySerializer, private modal: Modal, private router: Router) {
  }

  attached() {
    if (this.edit) {
      let params = this.router.currentInstruction.queryParams;
      this.transition = this.edit.kind.workflow.transitions.filter(item => item.id === params.transitionId)[0];
    }
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  get requiredMetadataIds(): number[] {
    if (this.edit && this.transition) {
      return this.requiredMetadataIdsForTransition();
    }
    return this.requiredMetadataIdsForAdding();
  }

  requiredMetadataIdsForTransition(): number[] {
    const reasonCollection = this.resource.blockedTransitions[this.transition.id];
    return reasonCollection ? reasonCollection.missingMetadataIds : [];
  }

  requiredMetadataIdsForAdding(): number[] {
    let restrictingMetadataIds = [];
    if (this.resource.kind && this.resource.kind.workflow) {
      const metadataListIds = this.resource.kind.metadataList.map(metadata => metadata.baseId);
      const restrictingMetadata = this.resource.kind.workflow.places[0].restrictingMetadataIds;
      restrictingMetadataIds = flatten(
        numberKeysByValue(restrictingMetadata, RequirementState.REQUIRED)
      ).filter(v => inArray(v, metadataListIds));
    }
    return restrictingMetadataIds;
  }

  get canApplyTransition(): boolean {
    if (this.transition) {
      const contents = this.copyContentsAndFilterEmptyValues(this.resource.contents);
      for (const metadatId of this.requiredMetadataIdsForTransition()) {
        let array = contents[metadatId] !== undefined ? contents[metadatId] : [];
        if (!array.length) {
          return false;
        }
      }
      return true;
    }
  }

  private copyContentsAndFilterEmptyValues(contents: StringArrayMap): StringArrayMap {
    let copiedContents = {};
    for (let index in contents) {
      copiedContents[index] = contents[index].filter(v => v !== undefined && v !== "");
    }
    return copiedContents;
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

  applyTransition() {
    if (this.transition) {
      this.validateAndSubmit(this.transition.id);
    }
  }

  saveResource() {
    this.validateAndSubmit();
  }

  private validateAndSubmit(transitionId?: string) {
    this.submitting = true;
    this.errorToDisplay = undefined;
    this.validator.validateObject(this.resource).then(results => {
      const errors = results.filter(result => !result.valid);
      if (errors.length == 0) {
        return this.submit({savedResource: this.resource, transitionId: transitionId})
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
