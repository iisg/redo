import {bindable, ComponentAttached} from "aurelia-templating";
import {computedFrom, observable} from "aurelia-binding";
import {RestrictingMetadataIdMap, Workflow, WorkflowPlace} from "../../workflow";
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataRepository} from "resources-config/metadata/metadata-repository";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowPlaceSorter} from "./workflow-place-sorter";
import {booleanAttribute} from "common/components/boolean-attribute";
import {twoWay} from "common/components/binding-mode";
import {deepCopy} from "common/utils/object-utils";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {debounce, flatten} from "lodash";
import {inArray} from "common/utils/array-utils";
import {ChangeEvent} from "common/events/change-event";
import {SystemMetadata} from "resources-config/metadata/system-metadata";

@autoinject
export class Excel implements ComponentAttached {
  @bindable(twoWay) workflow: Workflow;
  @bindable @booleanAttribute editable: boolean = false;
  @observable highlightedColumnId: number;
  metadataList: Metadata[];
  autoChangeRowToTheEnd: boolean = true;
  filterByResourceKinds: ResourceKind[] = [];
  resourceKindsLoaded = false;
  resourceKindsEmptyList = false;

  constructor(private metadataRepository: MetadataRepository,
              private workflowPlaceSorter: WorkflowPlaceSorter,
              private element: Element) {
  }

  attached() {
    this.metadataRepository.getListQuery()
      .filterByResourceClasses(this.workflow.resourceClass)
      .addSystemMetadataIds([SystemMetadata.REPRODUCTOR.id, SystemMetadata.VISIBILITY.id, SystemMetadata.TEASER_VISIBILITY.id])
      .onlyTopLevel()
      .get()
      .then(metadataList => metadataList.filter(m => !m.isDynamic))
      .then(metadataList => this.metadataList = metadataList);
  }

  onResourceKindsLoaded({detail: resourceKinds}) {
    this.resourceKindsLoaded = true;
    this.resourceKindsEmptyList = resourceKinds.length === 0;
  }

  @computedFrom('workflow.places')
  get orderedPlaces(): WorkflowPlace[] {
    return this.workflowPlaceSorter.getOrderedPlaces(this.workflow);
  }

  @computedFrom('metadataList', 'filterByResourceKinds', 'filterByResourceKinds.length')
  get filteredMetadata(): Metadata[] {
    if (this.filterByResourceKinds && this.filterByResourceKinds.length && !this.resourceKindsEmptyList) {
      const metadataIds = flatten(this.filterByResourceKinds.map(rk => rk.metadataList)).map(m => m.id);
      return this.metadataList.filter(metadata => inArray(metadata.id, metadataIds));
    } else {
      return this.metadataList;
    }
  }

  checkboxChanged(metadata: Metadata, changedPlace: WorkflowPlace): void {
    const newState = changedPlace.restrictingMetadataIds[metadata.id];
    if (this.autoChangeRowToTheEnd) {
      const changedPlaceIndex = this.orderedPlaces.indexOf(changedPlace);
      const placesToUpdate = this.orderedPlaces.slice(changedPlaceIndex + 1);
      for (const place of placesToUpdate) {
        const map: RestrictingMetadataIdMap = deepCopy(place.restrictingMetadataIds);
        map[metadata.id] = newState;
        place.restrictingMetadataIds = map;
      }
    }
    this.dispatchChangedEvent(this.workflow);
  }

  dispatchChangedEvent = debounce((value) => this.element.dispatchEvent(ChangeEvent.newInstance(value)), 10);
}
