import {MultilingualText} from "../resources-config/metadata/metadata";
import {WorkflowPlace, RequirementState} from "./workflow";
import {keysByValue} from "../common/utils/object-utils";

export function workflowPlaceToEntity(backendPlace: BackendPlace): WorkflowPlace {
  const place: WorkflowPlace = {
    id: backendPlace.id,
    label: backendPlace.label,
    restrictingMetadataIds: {}
  };
  for (const requiredId of backendPlace.requiredMetadataIds) {
    place.restrictingMetadataIds[requiredId] = RequirementState.REQUIRED;
  }
  for (const lockedId of backendPlace.lockedMetadataIds) {
    place.restrictingMetadataIds[lockedId] = RequirementState.LOCKED;
  }
  for (const assigneeId of backendPlace.assigneeMetadataIds) {
    place.restrictingMetadataIds[assigneeId] = RequirementState.ASSIGNEE;
  }
  return place;
}

export function workflowPlaceToBackend(place: WorkflowPlace): BackendPlace {
  const backendPlace: BackendPlace = {
    id: place.id,
    label: place.label,
    requiredMetadataIds: [],
    lockedMetadataIds: [],
    assigneeMetadataIds: []
  };
  backendPlace.requiredMetadataIds = keysByValue(place.restrictingMetadataIds, RequirementState.REQUIRED);
  backendPlace.lockedMetadataIds = keysByValue(place.restrictingMetadataIds, RequirementState.LOCKED);
  backendPlace.assigneeMetadataIds = keysByValue(place.restrictingMetadataIds, RequirementState.ASSIGNEE);
  return backendPlace;
}

export interface BackendPlace {
  id: string;
  label: MultilingualText;
  requiredMetadataIds: number[];
  lockedMetadataIds: number[];
  assigneeMetadataIds: number[];
}
