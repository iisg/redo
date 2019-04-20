import {BindingEngine, computedFrom, Disposable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {ValidationController} from "aurelia-validation";
import {twoWay} from "common/components/binding-mode";
import {diff} from "common/utils/array-utils";
import {deepCopy} from "common/utils/object-utils";
import {Metadata, metadataConstraintDefaults, MetadataConstraints, SupportedMetadataConstraintDefinition} from "../metadata";

@autoinject
export class MetadataConstraintForm implements ComponentAttached, ComponentDetached {
  @bindable(twoWay) metadata: Metadata;
  @bindable originalMetadata: Metadata;
  @bindable validationController: ValidationController;

  private controlSubscription: Disposable;

  /* This property is used to temporarily store values for constraints that were configured, but are no more applicable because control
   * has changed. For example choose control 'relationship' and configure required resource kind. Then change control to 'text'. Required
   * resource kind is no longer applicable, so it's removed from metadata and stored here. If you change control back to 'relationship',
   * previously chosen resource kind requirement can be restored from this variable. This doesn't get sent to backend and isn't shared
   * between form instances.
   */
  private deletedConstraints: MetadataConstraints = new MetadataConstraints();

  constructor(private bindingEngine: BindingEngine) {
  }

  @computedFrom('metadata.control')
  get supportedConstraints(): SupportedMetadataConstraintDefinition[] {
    return MetadataConstraints.getSupportedConstraints(this.metadata);
  }

  @computedFrom('metadata.control')
  get supportedConstraintsNamesWithConfiguration(): string[] {
    return this.supportedConstraints.filter(c => c.hasConfiguration).map(c => c.name);
  }

  @computedFrom('metadata.control')
  get supportedConstraintsNamesWithoutConfiguration(): string[] {
    return this.supportedConstraints.filter(c => !c.hasConfiguration).map(c => c.name);
  }

  private disposeControlSubscription(): void {
    if (this.controlSubscription !== undefined) {
      this.controlSubscription.dispose();
      this.controlSubscription = undefined;
    }
  }

  private subscribeToControlUpdates(): void {
    this.disposeControlSubscription();
    this.metadataControlChanged();
    this.controlSubscription = this.bindingEngine
      .propertyObserver(this.metadata, 'control')
      .subscribe(() => this.metadataControlChanged());
  }

  private metadataControlChanged(): void {
    this.updateConstraints(this.supportedConstraints);
  }

  attached(): void {
    this.subscribeToControlUpdates();
  }

  detached(): void {
    this.disposeControlSubscription();
  }

  metadataChanged(): void {
    this.subscribeToControlUpdates();
  }

  updateConstraints(expectedConstraints: SupportedMetadataConstraintDefinition[]) {
    const expectedConstraintsNames = expectedConstraints.map(c => c.name);
    const currentConstraints: string[] = Object.keys(this.metadata.constraints);
    const missingConstraints = diff(expectedConstraintsNames, currentConstraints);
    const extraConstraints = diff(currentConstraints, expectedConstraintsNames);
    for (const extraConstraint of extraConstraints) {
      this.deletedConstraints[extraConstraint] = this.metadata.constraints[extraConstraint];
      delete this.metadata.constraints[extraConstraint];
    }
    for (const missingConstraint of missingConstraints) {
      this.metadata.constraints[missingConstraint] = (missingConstraint in this.deletedConstraints)
        ? this.deletedConstraints[missingConstraint]
        : deepCopy(metadataConstraintDefaults[missingConstraint]);
    }
  }
}
