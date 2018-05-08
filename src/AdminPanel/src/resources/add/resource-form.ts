import {Resource} from "../resource";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {EntitySerializer} from "common/dto/entity-serializer";
import {ImportDialog} from "./xml-import/import-dialog";
import {Modal} from "common/dialog/modal";
import {ImportConfirmationDialog, ImportConfirmationDialogModel} from "./xml-import/import-confirmation-dialog";
import {ImportResult} from "./xml-import/xml-import-client";
import {deepCopy} from "common/utils/object-utils";
import {convertToObject, flatten, inArray} from "common/utils/array-utils";
import {RequirementState, WorkflowTransition} from "../../workflows/workflow";
import {Router} from "aurelia-router";
import {numberKeysByValue} from "../../common/utils/object-utils";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {MetadataValue} from "../metadata-value";

@autoinject
export class ResourceForm {
  @bindable resourceClass: string;
  @bindable parent: Resource;
  @bindable edit: Resource;
  @bindable submit: (value: {savedResource: Resource, transitionId: string}) => Promise<any>;
  @bindable cancel: () => void;
  resource: Resource = new Resource();
  submitting: boolean = false;
  validationError: boolean = false;
  transition: WorkflowTransition;
  resourceKindIdsAllowedByParent: number[];

  private validationController: ValidationController;

  constructor(private entitySerializer: EntitySerializer,
              private modal: Modal,
              private router: Router,
              validationControllerFactory: ValidationControllerFactory) {
    this.validationController = validationControllerFactory.createForCurrentScope();
    this.validationController.addRenderer(new BootstrapValidationRenderer());
  }

  attached() {
    if (this.edit && this.edit.kind.workflow) {
      let params = this.router.currentInstruction.queryParams;
      this.transition = this.edit.kind.workflow.transitions.filter(item => item.id === params.transitionId)[0];
    }
    this.setResourceKindsAllowedByParent();
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  @computedFrom('resource.kind')
  get requiredMetadataIds(): number[] {
    if (!this.resource.kind || !this.resource.kind.workflow) {
      return [];
    }
    let restrictingMetadata: NumberMap<any> = {};
    if (this.edit && this.transition) {
      const places = [];
      this.transition.tos.forEach(toId => {
        const workflowPlaces = this.resource.kind.workflow.places;
        places.push(workflowPlaces.find(place => place.id === toId));
      });
      restrictingMetadata = convertToObject(places.map(v => v.restrictingMetadataIds));
    } else if (!this.edit) {
      restrictingMetadata = this.resource.kind.workflow.places[0].restrictingMetadataIds;
    }
    const resourceKindMedatadaIds = this.resource.kind.metadataList.map(metadata => metadata.id);
    return flatten(
      [
        numberKeysByValue(restrictingMetadata, RequirementState.REQUIRED),
        numberKeysByValue(restrictingMetadata, RequirementState.ASSIGNEE)
      ]
    ).filter(metadataId => resourceKindMedatadaIds.includes(metadataId));
  }

  requiredMetadataIdsForTransition(): number[] {
    const reasonCollection = this.resource.blockedTransitions[this.transition.id];
    return reasonCollection ? reasonCollection.missingMetadataIds : [];
  }

  get canApplyTransition(): boolean {
    const requiredMetadataIds = this.transition ? this.requiredMetadataIdsForTransition() : this.requiredMetadataIds;
    const contents = this.copyContentsAndFilterEmptyValues(this.resource.contents);
    for (const metadataId of requiredMetadataIds) {
      let array = contents[metadataId] !== undefined ? contents[metadataId] : [];
      if (!array.length) {
        return false;
      }
    }
    return true;
  }

  get showRequiredMetadataAndWorkflowInfo(): boolean {
    return (!!this.transition || !this.editing) && this.resource.kind && !!this.resource.kind.workflow;
  }

  private setResourceKindsAllowedByParent() {
    this.resourceKindIdsAllowedByParent = undefined;
    if (this.parent) {
      let metadata = this.parent.kind.metadataList.find(v => v.id === SystemMetadata.PARENT.id);
      let resourceKindsAllowedByParent: any[] = metadata.constraints.resourceKind;
      this.resourceKindIdsAllowedByParent = resourceKindsAllowedByParent.map(v => v.id || v);
    }
  }

  copyParentResourceToChildResource() {
    if (this.parent) {
      this.parent.kind.metadataList.forEach(v => {
        if (v.copyToChildResource) {
          this.resource.contents[v.id] = deepCopy(this.parent.contents[v.id]);
        }
      });
    }
  }

  createResourceKindFilter() {
    return (resourceKind: ResourceKind) => {
      const isAllowedByParent = !Array.isArray(this.resourceKindIdsAllowedByParent)
        || inArray(resourceKind.id, this.resourceKindIdsAllowedByParent);
      const isNotSystemRK = resourceKind.id > 0;
      return isAllowedByParent && isNotSystemRK;
    };
  }

  private copyContentsAndFilterEmptyValues(contents: NumberMap<MetadataValue[]>): NumberMap<MetadataValue[]> {
    let copiedContents = {};
    for (let index in contents) {
      copiedContents[index] = contents[index].filter(v => v !== undefined && v.value !== undefined && v.value !== "");
    }
    return copiedContents;
  }

  resourceClassChanged() {
    this.resource = new Resource();
    this.resource.resourceClass = this.resourceClass;
  }

  editChanged(newValue: Resource) {
    this.resource = this.entitySerializer.clone(newValue);
    this.resourceClass = this.resource.resourceClass;
    this.parentChanged(this.parent);
  }

  parentChanged(newParent: Resource) {
    if (newParent != undefined) {
      this.resource.contents[SystemMetadata.PARENT.id] = [new MetadataValue(newParent.id)];
      this.copyParentResourceToChildResource();
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
    this.validationError = false;
    this.validationController.validate().then(result => {
      if (result.valid) {
        return this.submit({savedResource: this.resource, transitionId: transitionId})
          .then(() => this.editing || (this.resource = new Resource));
      } else {
        this.validationError = true;
      }
    }).finally(() => this.submitting = false);
  }

  openImportDialog() {
    this.modal.open(ImportDialog, {resourceKind: this.resource.kind}).then((importResult: ImportResult) => {
      const model: ImportConfirmationDialogModel = {
        metadataList: this.resource.kind.metadataList,
        importResult,
        invalidMetadataKeys: importResult.invalidMetadataKeys,
        resourceKind: this.resource.kind,
        resourceClass: this.resourceClass,
      };
      return this.modal.open(ImportConfirmationDialog, model);
    }).then(valueMap => this.importValues(valueMap));
  }

  importValues(valueMap: StringArrayMap): void {
    const metadataIds = this.resource.kind.metadataList.map(metadata => metadata.id);
    for (const metadataId in valueMap) {
      if (!inArray(parseInt(metadataId), metadataIds)) {
        continue;
      }
      const importedValues = valueMap[metadataId];
      const currentValues = this.resource.contents[metadataId].map(v => v.value);
      for (const metadataValue of importedValues) {
        if (!inArray(metadataValue.value, currentValues)) {
          this.resource.contents[metadataId].push(metadataValue);
        }
      }
    }
  }
}
