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
import {ChangeLossPreventer} from "../../common/change-loss-preventer/change-loss-preventer";
import {ChangeLossPreventerForm} from "../../common/form/change-loss-preventer-form";
import {numberKeysByValue} from "../../common/utils/object-utils";
import {BootstrapValidationRenderer} from "../../common/validation/bootstrap-validation-renderer";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {RequirementState, WorkflowPlace, WorkflowTransition} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ImportConfirmationDialog, ImportConfirmationDialogModel} from "./xml-import/import-confirmation-dialog";
import {ImportDialog} from "./xml-import/import-dialog";
import {ImportResult} from "./xml-import/xml-import-client";
import {CurrentUserIsReproductorValueConverter} from "../list/current-user-is-reproductor";
import {DisabilityReason} from "../../common/components/buttons/toggle-button";
import {HasRoleValueConverter} from "../../common/authorization/has-role-value-converter";

@autoinject
export class ResourceForm extends ChangeLossPreventerForm {
  @bindable({changeHandler: 'updateResource'}) resourceClass: string;
  @bindable parent: Resource;
  @bindable currentlyEditedResource: Resource;
  @bindable skipValidation: boolean;
  @bindable deposit: boolean;
  @bindable transition: WorkflowTransition;
  @bindable submit: (value: {
    savedResource: Resource,
    transitionId: string,
    newResourceKind?: ResourceKind,
    places?: WorkflowPlace[]
  }) => Promise<any>;
  @bindable clone: (value: {
    editedResource: Resource
  }) => Promise<any>;
  @bindable cancel: () => void;
  @bindable treeQueryUrl: string;
  submitting: boolean = false;
  cloning: boolean = false;
  disabled: boolean = false;
  validationError: boolean = false;
  places: WorkflowPlace[] = [];
  resourceKindIdsAllowedByParent: number[];
  resource: Resource;

  private validationController: ValidationController;

  constructor(private entitySerializer: EntitySerializer,
              private modal: Modal,
              private router: Router,
              private changeLossPreventer: ChangeLossPreventer,
              private hasRole: HasRoleValueConverter,
              private isReproductor: CurrentUserIsReproductorValueConverter,
              validationControllerFactory: ValidationControllerFactory) {
    super();
    this.validationController = validationControllerFactory.createForCurrentScope();
    this.validationController.addRenderer(new BootstrapValidationRenderer());
  }

  attached() {
    if (this.currentlyEditedResource
      && this.currentlyEditedResource.kind
      && this.currentlyEditedResource.kind.workflow
      && !this.deposit) {
      let params = this.router.currentInstruction.queryParams;
      this.places = this.currentlyEditedResource.currentPlaces;
      this.transition = this.currentlyEditedResource.availableTransitions.filter(item => item.id === params.transitionId)[0];
    }
    this.setResourceKindsAllowedByParent();
    this.changeLossPreventer.enable(this);
  }

  @computedFrom('transition', 'resource.kind.workflow', 'resource.currentPlaces')
  get targetPlaces() {
    if (!this.resource.kind || !this.resource.kind.workflow) {
      return [];
    }
    if (this.transition) {
      return this.transition.tos.map((value) => {
        const workflowPlaces = this.resource.kind.workflow.places;
        return workflowPlaces.find(place => place.id === value);
      });
    }
    if (!this.currentlyEditedResource || !this.resource.currentPlaces) {
      return [this.resource.kind.workflow.places[0]];
    }
    return [];
  }

  @computedFrom('resource.id')
  get editing(): boolean {
    return !!this.resource.id;
  }

  @computedFrom('resource.kind', 'targetPlaces', 'targetPlaces.length')
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
    return ((!!this.transition && this.transition.id !== 'update')
      || !this.editing) && this.resource.kind
      && !!this.resource.kind.workflow
      && !this.skipValidation;
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
    if (this.parent && !this.editing) {
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

  updateResource() {
    this.resource = this.resource ? this.resource : new Resource();
    this.resource.resourceClass = this.resourceClass;
  }

  currentlyEditedResourceChanged(newValue: Resource) {
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
    this.disabled = true;
    this.validationError = false;
    if (this.skipValidation) {
      this.resource.contents = this.copyContentsAndFilterEmptyValues(this.resource.contents);
      this.changeLossPreventer.disable();
      return this.submit({savedResource: this.resource, transitionId, newResourceKind: this.resource.kind, places: this.places})
        .then(() => this.editing || (this.resource = new Resource)).finally(() => this.submitting = false);
    } else {
      this.validationController.validate().then(result => {
        if (result.valid) {
          this.changeLossPreventer.disable();
          return this.submit({savedResource: this.resource, transitionId})
            .then(() => this.editing || (this.resource = new Resource));
        } else {
          this.validationError = true;
        }
      }).finally(() => {
        this.submitting = false;
        this.disabled = false;
      });
    }
  }

  @computedFrom("parent", "resource_class")
  get disabilityReason(): DisabilityReason {
    return this.parent && !this.isReproductor.toView(this.parent)
    || (!this.parent && !this.hasRole.toView('ADMIN', this.resourceClass))
      ? {icon: 'user-2', message: 'You do not have permissions to clone resource.'}
      : undefined;
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

  @computedFrom("disabled", "parent", "parent.pendingRequest")
  get cloningResourceDisabled(): boolean {
    return this.disabled
      || (this.parent && (this.parent.pendingRequest || !this.isReproductor.toView(this.parent)))
      || (!this.parent && !this.hasRole.toView('ADMIN', this.resourceClass));
  }

  cloneResource() {
    this.cloning = true;
    this.disabled = true;
    this.validationError = false;
    this.validationController.validate().then(result => {
      if (result.valid) {
        this.changeLossPreventer.disable();
        return this.clone({editedResource: this.resource})
          .then(() => this.editing || (this.resource = new Resource));
      } else {
        this.validationError = true;
      }
    }).finally(() => {
      this.disabled = false;
      this.cloning = false;
    });
  }
}
