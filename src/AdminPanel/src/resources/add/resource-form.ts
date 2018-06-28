import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {Modal} from "common/dialog/modal";
import {EntitySerializer} from "common/dto/entity-serializer";
import {convertToObject, flatten, inArray} from "common/utils/array-utils";
import {deepCopy} from "common/utils/object-utils";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {numberKeysByValue} from "../../common/utils/object-utils";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {RequirementState, WorkflowTransition, WorkflowPlace} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ImportConfirmationDialog, ImportConfirmationDialogModel} from "./xml-import/import-confirmation-dialog";
import {ImportDialog} from "./xml-import/import-dialog";
import {ImportResult} from "./xml-import/xml-import-client";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";

@autoinject
export class ResourceForm extends ChangeLossPreventerForm {
  @bindable resourceClass: string;
  @bindable parent: Resource;
  @bindable edit: Resource;
  @bindable skipValidation: boolean;
  @bindable submit: (value: {
    savedResource: Resource,
    transitionId: string,
    newResourceKind?: ResourceKind,
    places?: WorkflowPlace[]
  }) => Promise<any>;
  @bindable cancel: () => void;
  submitting: boolean = false;
  validationError: boolean = false;
  transition: WorkflowTransition;
  places: WorkflowPlace[] = [];
  resourceKindIdsAllowedByParent: number[];
  resource: Resource;

  private validationController: ValidationController;

  constructor(private entitySerializer: EntitySerializer,
              private modal: Modal,
              private router: Router,
              private changeLossPreventer: ChangeLossPreventer,
              validationControllerFactory: ValidationControllerFactory) {
    super();
    this.validationController = validationControllerFactory.createForCurrentScope();
    this.validationController.addRenderer(new BootstrapValidationRenderer());
  }

  attached() {
    if (this.edit && this.edit.kind && this.edit.kind.workflow) {
      let params = this.router.currentInstruction.queryParams;
      this.places = this.edit.currentPlaces;
      this.transition = this.edit.kind.workflow.transitions.filter(item => item.id === params.transitionId)[0];
    }
    this.setResourceKindsAllowedByParent();
    this.changeLossPreventer.enable(this);
  }

  @computedFrom('transition', 'resource.kind.workflow', 'resource.currentPlaces')
  get targetPlaces() {
    if (!this.resource.kind || !this.resource.kind.workflow) {
      return [];
    }
    if (!this.edit) {
      return [this.resource.kind.workflow.places[0]];
    }
    if (this.transition) {
      return this.transition.tos.map((value) => {
        const workflowPlaces = this.resource.kind.workflow.places;
        return workflowPlaces.find(place => place.id === value);
      });
    }
    return this.resource.currentPlaces;
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  @computedFrom('resource.kind', 'targetPlaces')
  get requiredMetadataIds(): number[] {
    if (this.resource.kind && !this.skipValidation) {
      const restrictingMetadata: NumberMap<any> = convertToObject(this.targetPlaces.map(v => v.restrictingMetadataIds));
      const resourceKindMedatadaIds = this.resource.kind.metadataList.map(metadata => metadata.id);
      return flatten(
        [
          numberKeysByValue(restrictingMetadata, RequirementState.REQUIRED),
          numberKeysByValue(restrictingMetadata, RequirementState.ASSIGNEE)
        ]
      ).filter(metadataId => inArray(metadataId, resourceKindMedatadaIds));
    }
    return [];
  }

  get showRequiredMetadataAndWorkflowInfo(): boolean {
    return (!!this.transition || !this.editing) && this.resource.kind && !!this.resource.kind.workflow && !this.skipValidation;
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

  validateAndSubmit() {
    const transitionId = this.transition && this.transition.id;
    this.submitting = true;
    this.validationError = false;
    if (this.skipValidation) {
      this.resource.contents = this.copyContentsAndFilterEmptyValues(this.resource.contents);
      return this.submit({savedResource: this.resource, transitionId, newResourceKind: this.resource.kind, places: this.places})
        .then(() => this.editing || (this.resource = new Resource)).finally(() => this.submitting = false);
    } else {
      this.validationController.validate().then(result => {
        if (result.valid) {
          return this.submit({savedResource: this.resource, transitionId})
            .then(() => this.editing || (this.resource = new Resource));
        } else {
          this.validationError = true;
        }
      }).finally(() => this.submitting = false);
    }
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
