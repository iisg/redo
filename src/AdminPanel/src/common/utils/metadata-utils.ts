import {ResourceKind} from "../../resources-config/resource-kind/resource-kind";
import {GroupMetadataList, Metadata, MetadataGroup} from "../../resources-config/metadata/metadata";

function getMergedMetadata(resourceKinds: ResourceKind[]): Metadata[] {
  let mergedMetadata = [];
  for (const resourceKind of resourceKinds) {
    for (const metadata of resourceKind.metadataList) {
      mergedMetadata = getUpdatedMetadataArray(mergedMetadata, metadata);
    }
  }
  return mergedMetadata;
}

export function getMergedBriefMetadata(resourceKinds: ResourceKind[]): Metadata[] {
  return getMergedMetadata(resourceKinds).filter(metadata => metadata.shownInBrief);
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

export function groupMetadata(metadataList: Metadata[], groupIds: string[]): GroupMetadataList[] {
  const orderedGroups = groupIds.map(id => ({groupId: id, metadataList: []}));
  const lookup = orderedGroups.reduce((obj, group) => ({...obj, [group.groupId]: group}), {});
  if (!lookup.hasOwnProperty(MetadataGroup.defaultGroupId)) {
    const defaultGroup = {groupId: MetadataGroup.defaultGroupId, metadataList: []};
    orderedGroups.push(defaultGroup);
    lookup[defaultGroup.groupId] = defaultGroup;
  }
  metadataList.forEach(metadata => {
    lookup[metadata.groupId].metadataList.push(metadata);
  });
  return orderedGroups.filter(group => group.metadataList.length > 0);
}
