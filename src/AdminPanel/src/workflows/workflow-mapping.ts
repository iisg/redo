import {MultilingualText} from "resources-config/metadata/metadata";
import {RequirementState, RestrictingMetadataIdMap} from "./workflow";
import {numberKeysByValue, deepCopy} from "common/utils/object-utils";
import {AdvancedMapper} from "common/dto/mappers";

export class RestrictingMetadataMapper extends AdvancedMapper<RestrictingMetadataIdMap> {
  fromBackendProperty(_, dto: BackendPlace): Promise<RestrictingMetadataIdMap> {
    const map: RestrictingMetadataIdMap = {};
    for (const requiredId of dto.requiredMetadataIds) {
      map[requiredId] = RequirementState.REQUIRED;
    }
    for (const lockedId of dto.lockedMetadataIds) {
      map[lockedId] = RequirementState.LOCKED;
    }
    for (const assigneeId of dto.assigneeMetadataIds) {
      map[assigneeId] = RequirementState.ASSIGNEE;
    }
    return Promise.resolve(map);
  }

  toBackendProperty(key: string, mergedMap: RestrictingMetadataIdMap, backendPlace: BackendPlace): void {
    backendPlace.requiredMetadataIds = numberKeysByValue(mergedMap[key], RequirementState.REQUIRED);
    backendPlace.lockedMetadataIds = numberKeysByValue(mergedMap[key], RequirementState.LOCKED);
    backendPlace.assigneeMetadataIds = numberKeysByValue(mergedMap[key], RequirementState.ASSIGNEE);
  }

  clone(map: RestrictingMetadataIdMap): RestrictingMetadataIdMap {
    return deepCopy(map);
  }
}

interface BackendPlace {
  id: string;
  label: MultilingualText;
  requiredMetadataIds: number[];
  lockedMetadataIds: number[];
  assigneeMetadataIds: number[];
}
