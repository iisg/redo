import {bindable} from "aurelia-templating";
import {BindingEngine, computedFrom, Disposable} from "aurelia-binding";
import {oneTime, twoWay} from "common/components/binding-mode";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "resources-config/metadata/metadata";
import {ResourceKindRepository} from "../../../resource-kind/resource-kind-repository";
import {MetadataRepository} from "../../metadata-repository";
import {MetadataValue} from "../../../../resources/metadata-value";

@autoinject
export class RelatedResourceMetadataFilterEditor {
  @bindable(twoWay) metadata: Metadata;
  @bindable(oneTime) originalFilters: NumberMap<string>;
  @bindable hasBase: boolean;

  filteredMetadata: Metadata;
  filterValue: MetadataValue = new MetadataValue();

  resourceKindConstraintObserver: Disposable;
  private resourceClasses: string[];

  constructor(private resourceKindRepository: ResourceKindRepository,
              private metadataRepository: MetadataRepository,
              private bindingEngine: BindingEngine) {
  }

  async metadataChanged() {
    this.disposeResourceKindConstraintObserver();
    this.resourceKindConstraintObserver = this.bindingEngine
      .propertyObserver(this.metadata.constraints, 'resourceKind')
      .subscribe(() => this.calculatePossibleResourceClasses());
    const currentFilterId = Object.keys(this.metadata.constraints.relatedResourceMetadataFilter)[0];
    if (currentFilterId) {
      this.filteredMetadata = await this.metadataRepository.get(currentFilterId);
      this.filterValue.value = this.metadata.constraints.relatedResourceMetadataFilter[currentFilterId];
    }
  }

  updateConstraint() {
    if (this.metadata) {
      this.metadata.constraints.relatedResourceMetadataFilter = {};
      if (this.filteredMetadata) {
        this.metadata.constraints.relatedResourceMetadataFilter[this.filteredMetadata.id] = this.filterValue.value;
      }
    }
  }

  private disposeResourceKindConstraintObserver() {
    if (this.resourceKindConstraintObserver) {
      this.resourceKindConstraintObserver.dispose();
      this.resourceKindConstraintObserver = undefined;
    }
  }

  attached() {
    this.calculatePossibleResourceClasses();
    this.filterValue.onChange(this.bindingEngine, () => this.updateConstraint());
  }

  detached() {
    this.disposeResourceKindConstraintObserver();
    this.filterValue.clearChangeListener();
  }

  resetToOriginalValues() {
    this.metadata.constraints.relatedResourceMetadataFilter = this.originalFilters;
    this.metadataChanged();
  }

  calculatePossibleResourceClasses() {
    const promises = [];
    for (let resourceKindOrId of this.metadata.constraints.resourceKind) {
      if (typeof resourceKindOrId == 'number') {
        promises.push(this.resourceKindRepository.get(resourceKindOrId as number).then(resourceKind => resourceKind.resourceClass));
      } else {
        promises.push(Promise.resolve(resourceKindOrId.resourceClass));
      }
    }
    Promise.all(promises).then(resourceClasses => {
      const unique = resourceClasses.filter((rc, index, array) => array.indexOf(rc) == index);
      if (this.filteredMetadata && unique.indexOf(this.filteredMetadata.resourceClass) < 0) {
        this.filteredMetadata = undefined;
        this.updateConstraint();
      }
      this.resourceClasses = unique;
    });
  }

  @computedFrom('metadata.constraints.relatedResourceMetadataFilter', 'originalFilters', 'filteredMetadata', 'filterValue.value')
  get wasModified(): boolean {
    return (!this.originalFilters && !!this.filteredMetadata)
      || (this.originalFilters && this.filteredMetadata && !this.originalFilters[this.filteredMetadata.id])
      || (this.originalFilters && this.filteredMetadata && this.originalFilters[this.filteredMetadata.id] != this.filterValue.value);
  }
}
