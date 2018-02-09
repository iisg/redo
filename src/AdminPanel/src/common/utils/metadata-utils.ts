import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {Metadata} from "../../resources-config/metadata/metadata";

function getMergedMetadata(resourceKindList: ResourceKind[]): Metadata[] {
  let mergedMetadata = [];
  for (const resourceKind of resourceKindList) {
    for (const metadata of resourceKind.metadataList) {
      mergedMetadata = getUpdatedMetadataArray(mergedMetadata, metadata);
    }
  }
  return mergedMetadata;
}

export function getMergedBriefMetadata(resourceKindList: ResourceKind[]): Metadata[] {
  return getMergedMetadata(resourceKindList).filter(metadata => metadata.shownInBrief);
}

export function getUpdatedMetadataArray(metadataList: Metadata[], metadata: Metadata): Metadata[] {
  const index = getIndexOfMetadataInArray(metadataList, metadata);
  if (index >= 0) {
    if (metadata.shownInBrief) {
      metadataList.splice(index, 1, metadata);
    }
  } else {
    metadataList.push(metadata);
  }
  return metadataList;
}

export function getIndexOfMetadataInArray(metadataList: Metadata[], metadata: Metadata): number {
  return metadataList.map(metadata => metadata.id).indexOf(metadata.id);
}
