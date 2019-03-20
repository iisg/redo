import {computedFrom, observable} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "common/components/boolean-attribute";
import {ChangeEvent} from "common/events/change-event";
import {inArray} from "common/utils/array-utils";
import {deepCopy} from "common/utils/object-utils";
import {debounce, flatten} from "lodash";
import {Metadata} from "resources-config/metadata/metadata";
import {SystemMetadata} from "resources-config/metadata/system-metadata";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {RestrictingMetadataIdMap, Workflow, WorkflowPlace} from "../../workflow";
import {WorkflowPlaceSorter} from "./workflow-place-sorter";

@autoinject
export class Excel {
  private readonly DISPLAYED_SYSTEM_METADATA_IDS = [SystemMetadata.REPRODUCTOR.id, SystemMetadata.VISIBILITY.id,
    SystemMetadata.TEASER_VISIBILITY.id];

  @bindable(twoWay) workflow: Workflow;
  @bindable resourceKinds: ResourceKind[];
  @bindable @booleanAttribute editable: boolean = false;
  @observable highlightedColumnId: number;
  metadataList: Metadata[];
  autoChangeRowToTheEnd: boolean = true;
  selectedResourceKinds: ResourceKind[];
  resourceKindsEmptyList = false;

  constructor(private workflowPlaceSorter: WorkflowPlaceSorter,
              private element: Element) {
  }

  bind() {
    if (this.resourceKinds) {
      this.resourceKindsChanged();
    }
  }

  resourceKindsChanged() {
    const metadataById: Map<number, Metadata> = new Map();
    this.resourceKinds.forEach(resourceKind =>
      resourceKind.metadataList.filter(metadata => !metadata.isDynamic
        && (metadata.id > 0 || this.DISPLAYED_SYSTEM_METADATA_IDS.includes(metadata.id)))
        .forEach(metadata => metadataById.set(metadata.id, metadata)));
    this.metadataList = Array.from(metadataById.values());
  }

  @computedFrom('workflow.places')
  get orderedPlaces(): WorkflowPlace[] {
    return this.workflowPlaceSorter.getOrderedPlaces(this.workflow);
  }

  @computedFrom('metadataList', 'selectedResourceKinds', 'selectedResourceKinds.length')
  get filteredMetadata(): Metadata[] {
    if (this.resourceKinds.length && this.selectedResourceKinds && this.selectedResourceKinds.length) {
      const metadataIds = flatten(this.selectedResourceKinds.map(resourceKind => resourceKind.metadataList)).map(metadata => metadata.id);
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
