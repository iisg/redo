import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Metadata} from "resources-config/metadata/metadata";
import {getMergedBriefMetadata, groupMetadata} from "./metadata-utils";

describe('metadata-utils', () => {
  describe(getMergedBriefMetadata.name, () => {
    function metadataMock(id: number, shownInBrief: boolean): Metadata {
      const metadata = new Metadata();
      metadata.id = id;
      metadata.shownInBrief = shownInBrief;
      return metadata;
    }

    function resourceKindMock(id: number, metadataList: Metadata[]): ResourceKind {
      const resourceKind = new ResourceKind();
      resourceKind.id = id;
      resourceKind.metadataList = metadataList;
      return resourceKind;
    }

    it('replaces non-brief metadata with brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = metadataMock(0, false);
      const metadata2 = metadataMock(1, true);
      testResourceKindList[0] = resourceKindMock(0, [metadata1, metadata2]);
      const metadata3 = metadataMock(0, true);
      testResourceKindList[1] = resourceKindMock(1, [metadata3]);
      const expectedMetadataList = [metadata3, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });

    it('does not replace brief metadata with non-brief ones', () => {
      const testResourceKindList = [] as ResourceKind[];
      const metadata1 = metadataMock(0, true);
      const metadata2 = metadataMock(1, true);
      testResourceKindList[0] = resourceKindMock(0, [metadata1, metadata2]);
      const metadata3 = metadataMock(0, false);
      testResourceKindList[1] = resourceKindMock(1, [metadata3]);
      const expectedMetadataList = [metadata1, metadata2];
      const result = getMergedBriefMetadata(testResourceKindList);
      expect(result).toEqual(expectedMetadataList);
    });
  });
  describe(groupMetadata.name, () => {
    function metadataMock(id: number, groupId: string, parentId: number = undefined): Metadata {
      const metadata = new Metadata();
      metadata.id = id;
      metadata.groupId = groupId;
      metadata.parentId = parentId;
      return metadata;
    }

    it('groups metadata by groupId', () => {
      const metadata1 = metadataMock(0, 'group1');
      const metadata2 = metadataMock(1, 'group2');
      const metadata3 = metadataMock(2, 'group2');
      const expectedGroups = [
        {groupId: 'group1', metadataList: [metadata1], childMetadata: false},
        {groupId: 'group2', metadataList: [metadata2, metadata3], childMetadata: false}
      ];
      const result = groupMetadata([metadata1, metadata2, metadata3], ['group1', 'group2']);
      expect(result).toEqual(expectedGroups);
    });

    it('handles empty lists', () => {
      expect(groupMetadata([], [])).toEqual([]);
    });

    it('uses group order from list', () => {
      const metadata1 = metadataMock(0, 'bbb');
      const metadata2 = metadataMock(1, 'aaa');
      const metadata3 = metadataMock(2, 'ccc');
      const groupIds = ['aaa', 'ccc', 'bbb'];
      const expectedGroups = [
        {groupId: 'aaa', metadataList: [metadata2], childMetadata: false},
        {groupId: 'ccc', metadataList: [metadata3], childMetadata: false},
        {groupId: 'bbb', metadataList: [metadata1], childMetadata: false}
      ];
      const result = groupMetadata([metadata1, metadata2, metadata3], groupIds);
      expect(result).toEqual(expectedGroups);
    });

    it('sets "childMetadata" to true when child metadata exists', () => {
      const metadata1 = metadataMock(0, 'group1', 3);
      const metadata2 = metadataMock(1, 'group2');
      const metadata3 = metadataMock(2, 'group2');
      const metadata4 = metadataMock(2, 'group1');
      const groupIds = ['group1', 'group2'];
      const expectedGroups = [
        {groupId: 'group1', metadataList: [metadata1, metadata4], childMetadata: true},
        {groupId: 'group2', metadataList: [metadata2, metadata3], childMetadata: false}
      ];
      const result = groupMetadata([metadata1, metadata2, metadata3, metadata4], groupIds);
      expect(result).toEqual(expectedGroups);
    });
  });
});
