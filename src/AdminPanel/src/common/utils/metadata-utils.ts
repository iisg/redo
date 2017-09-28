import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {Metadata} from "../../resources-config/metadata/metadata";
import {inArray} from "./array-utils";

export function getMergedMetadata(resourceKindList: ResourceKind[]): Metadata[] {
  let mergedMetadata = [];
  for (const resourceKind of resourceKindList) {
    for (const metadata of resourceKind.metadataList) {
      if (!metadataAlreadyInArray(mergedMetadata, metadata)) {
        mergedMetadata.push(metadata);
      }
    }
  }
  return mergedMetadata;
}

export function getMergedBriefMetadata(resourceKindList: ResourceKind[]): Metadata[] {
  return getMergedMetadata(resourceKindList).filter(metadata => metadata.shownInBrief);
}

export function metadataAlreadyInArray(metadataList: Metadata[], metadata: Metadata): boolean {
  return inArray(metadata.baseId, metadataList.map(metadata => metadata.baseId));
}
