import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {ValidationController, ValidationControllerFactory} from "aurelia-validation";
import {Modal} from "common/dialog/modal";
import {EntitySerializer} from "common/dto/entity-serializer";
import {convertToObject, flatten, inArray} from "common/utils/array-utils";
import {deepCopy, numberKeysByValue} from "common/utils/object-utils";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {ChangeLossPreventer} from "common/change-loss-preventer/change-loss-preventer";
import {ChangeLossPreventerForm} from "common/form/change-loss-preventer-form";
import {BootstrapValidationRenderer} from "common/validation/bootstrap-validation-renderer";
import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {RequirementState, WorkflowPlace, WorkflowTransition} from "../../workflows/workflow";
import {MetadataValue} from "../metadata-value";
import {Resource} from "../resource";
import {ImportConfirmationDialog, ImportConfirmationDialogModel} from "./xml-import/import-confirmation-dialog";
import {ImportDialog, ImportDialogModel} from "./xml-import/import-dialog";
import {ImportResult} from "./xml-import/xml-import-client";
import {HasRoleValueConverter} from "common/authorization/has-role-value-converter";
import {EventAggregator} from "aurelia-event-aggregator";
import {CustomEvent} from "../../common/events/custom-event";
import {Metadata} from "../../resources-config/metadata/metadata";

@autoinject
export class ResourceForm extends ChangeLossPreventerForm implements ComponentAttached, ComponentDetached {
  @bindable({changeHandler: 'updateResource'}) resourceClass: string;
  @bindable parent: Resource;
  @bindable resourceKind: ResourceKind;
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
  @bindable cancel: () => void;
  @bindable forceSimpleFileUpload: boolean = false;
  @bindable forceShowingGroups: boolean = false;
  @bindable metadataDisplayFilter: (metadata: Metadata) => boolean;
  submitting: boolean = false;
  disabled: boolean = false;
  validationError: boolean = false;
  places: WorkflowPlace[] = [];
  resource: Resource;
  validationRenderer: BootstrapValidationRenderer;

  private validationController: ValidationController;

  constructor(private entitySerializer: EntitySerializer,
              private modal: Modal,
              private changeLossPreventer: ChangeLossPreventer,
              private hasRole: HasRoleValueConverter,
              private eventAggregator: EventAggregator,
              private element: Element,
              validationControllerFactory: ValidationControllerFactory) {
    super();
    this.validationController = validationControllerFactory.createForCurrentScope();
  }

  attached() {
    if (!this.validationRenderer && !this.skipValidation) {
      this.validationRenderer = new BootstrapValidationRenderer();
      this.validationController.addRenderer(this.validationRenderer);
    }
    if (this.currentlyEditedResource
      && this.currentlyEditedResource.kind
      && this.currentlyEditedResource.kind.workflow
      && !this.deposit) {
      this.places = this.currentlyEditedResource.currentPlaces;
    }
    this.changeLossPreventer.enable(this);
    this.eventAggregator.publish('resourceFormOpened', true);
  }

  detached() {
    this.eventAggregator.publish('resourceFormOpened', false);
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

  resourceKindChanged() {
    this.resource.kind = this.resourceKind;
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
    if (this.submitting) {
      return;
    }
    this.submitting = true;
    this.disabled = true;
    this.validationError = false;
    if (this.skipValidation) {
      this.resource.contents = this.copyContentsAndFilterEmptyValues(this.resource.contents);
      this.changeLossPreventer.disable();
      return this.submit({savedResource: this.resource, transitionId, newResourceKind: this.resource.kind, places: this.places})
        .then(() => this.editing || (this.resource = new Resource))
        .finally(() => this.submitting = false);
    } else {
      this.element.dispatchEvent(CustomEvent.newInstance('submitting', true));
      this.validationController.validate().then(result => {
        if (result.valid) {
          this.changeLossPreventer.disable();
          return this.submit({savedResource: this.resource, transitionId})
            .then(() => this.editing || (this.resource = new Resource));
        } else {
          this.validationError = true;
          this.element.dispatchEvent(CustomEvent.newInstance('submitting', false));
        }
      }).finally(() => {
        this.submitting = false;
        this.disabled = false;
        this.eventAggregator.publish('resourceFormOpened', false);
      });
    }
  }

  openImportDialog() {
    this.modal.open(ImportDialog, {resourceKind: this.resource.kind} as ImportDialogModel).then((importResult: ImportResult) => {
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
    const assigneeMetadataIds = WorkflowPlace.getPlacesRequirementState(this.targetPlaces, RequirementState.ASSIGNEE);
    const autoAssignMetadataIds = WorkflowPlace.getPlacesRequirementState(this.targetPlaces, RequirementState.AUTOASSIGN);
    const lockedMetadataIds = WorkflowPlace.getPlacesRequirementState(this.targetPlaces, RequirementState.LOCKED)
      .concat(assigneeMetadataIds).concat(autoAssignMetadataIds);
    const metadataIds = this.resource.kind.metadataList.map(metadata => metadata.id);
    for (const metadataId in valueMap) {
      if (!inArray(parseInt(metadataId), metadataIds)) {
        continue;
      }
      const importedValues = valueMap[metadataId];
      const currentValues = this.resource.contents[metadataId].map(v => v.value);
      for (const metadataValue of importedValues) {
        if (!inArray(+metadataId, lockedMetadataIds) && !inArray(metadataValue.value, currentValues)) {
          if (this.resource.contents[metadataId].length) {
            this.resource.contents[metadataId] = this.resource.contents[metadataId].filter(metadataValue => metadataValue.value);
          }
          this.resource.contents[metadataId].push(metadataValue);
        }
      }
    }
  }

  cancelForm() {
    this.changeLossPreventer.canLeaveView().then(canLeave => {
      if (canLeave) {
        this.cancel();
      }
    });
  }

  onChange() {
    this.dirty = true;
    this.element.dispatchEvent(CustomEvent.newInstance('resource-contents-changed', this.resource));
  }
}
